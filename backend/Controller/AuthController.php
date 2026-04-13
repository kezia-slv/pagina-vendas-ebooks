<?php
namespace Datislopo\Ebook\Controllers;

use Datislopo\Ebook\Models\Usuario;
use Datislopo\Ebook\Core\Flash;
use Datislopo\Ebook\Database\Database;
use Datislopo\Ebook\Core\View;
use Datislopo\Ebook\Core\Redirect;
use Datislopo\Ebook\Core\Session;

class AuthController
{
    private Usuario $usuarioModel;
    private Session $session;

    public function __construct()
    {
        $db = Database::getInstance();
        $this->usuarioModel = new Usuario($db);
        $this->session = new Session();
    }

    /**
     * Renderiza a página de login.
     */
    public function login(): void
    {
        View::render('admin/auth/login');
    }

    /**
     * Renderiza a página de cadastro (registro).
     */
    public function register(): void
    {
        View::render('admin/auth/register');
    }

    /**
     * Realiza o logout do usuário e redireciona para login.
     */
    public function logout(): void
    {
        $this->session->destroy();
        Redirect::redirecionarComMensagem('/backend/login', 'success', 'Você saiu da sua conta! Volte sempre');
    }

    /**
     * Processa a autenticação do usuário (login).
     * Verifica credenciais e cria a sessão.
     */
    public function authenticar(): void
    {
        $email = $_POST['email_usuario'] ?? null;
        $senha = $_POST['senha_usuario'] ?? null;
        $usuario = $this->usuarioModel->checarCredenciais($email, $senha);

        if ($usuario) {
            session_regenerate_id(true);
            $this->session->set('usuario_id', $usuario['id_usuario']);
            $this->session->set('usuario_nome', $usuario['nome_usuario']);
            $this->session->set('usuario_tipo', $usuario['tipo_usuario']);
            $this->session->set('usuario_email', $usuario['email_usuario']);

            if ($usuario['tipo_usuario'] === 'Admin') {
                Redirect::redirecionarPara('/backend/ebook');
            }
            else {
                Redirect::redirecionarPara('/index.html');
            }
        }
        else {
            $_SESSION['flash_error'] = 'Email ou senha incorretos';
            Redirect::redirecionarPara('/backend/login');
        }
    }

    /**
     * Processa o cadastro de um novo usuário.
     * Valida senhas e unicidade de email.
     */
    public function cadastrarUsuario()
    {
        try {
            $nome = $_POST['nome_usuario'] ?? null;
            $email = $_POST['email_usuario'] ?? null;
            $senha = $_POST['senha_usuario'] ?? null;
            $senha_confirm = $_POST['senha_confirm'] ?? null;

            if ($senha != $senha_confirm) {
                return Redirect::redirecionarComMensagem(url('backend/register'), 'erros', 'As senhas não conferem.');
            }

            if (!empty($this->usuarioModel->buscarUsuariosPorEMail($email))) {
                return Redirect::redirecionarComMensagem(url('backend/register'), 'erros', 'O e-mail informado já está em uso.');
            }

            $novoUsuarioId = $this->usuarioModel->inseriUsuario($nome, $email, $senha, 'Cliente');

            if ($novoUsuarioId) {
                Redirect::redirecionarComMensagem(url('backend/login'), 'success', 'Cadastro realizado! Por favor, faça o login.');
            }
            else {
                Redirect::redirecionarComMensagem(url('backend/register'), 'error', 'Erro no servidor ao criar conta. Tente novamente.');
            }

        }
        catch (\Throwable $e) {
            error_log("EXCEÇÃO CRÍTICA EM cadastrarUsuario: " . $e->getMessage());
            Redirect::redirecionarComMensagem(url('backend/register'), 'error', 'Erro interno no servidor. Detalhes foram logados.');
        }
    }

    /**
     * Verifica o status da sessão (API).
     * Retorna JSON com dados do usuário ou authenticated: false.
     */
    public function checkSession(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $usuarioId = $this->session->get('usuario_id');

        if ($usuarioId) {
            $nome = $this->session->get('usuario_nome');
            $tipo = $this->session->get('usuario_tipo');
            $email = $this->session->get('usuario_email');

            echo json_encode([
                'authenticated' => true,
                'user' => [
                    'id' => $usuarioId,
                    'name' => $nome,
                    'email' => $email,
                    'role' => $tipo
                ]
            ]);
        }
        else {
            echo json_encode([
                'authenticated' => false
            ]);
        }
    }

    /**
     * Corrige o caminho da imagem para garantir que funcione no frontend.
     */
    private function corrigirCaminhoImagem($caminho)
    {
        if (empty($caminho)) {
            return '/img/avatar_placeholder.png';
        }

        if (strpos($caminho, 'http') === 0) {
            return $caminho;
        }

        // Se o caminho já for absoluto (começar com /) e não for /backend, adiciona /backend
        if (strpos($caminho, '/') === 0) {
            if (strpos($caminho, '/backend') === 0) {
                return $caminho;
            }
            return '/backend' . $caminho;
        }

        // Se for um caminho relativo, assume que está dentro de backend
        return '/backend/' . $caminho;
    }

    // ===== ESQUECI A SENHA =====

    /**
     * Renderiza o formulário de "Esqueci a senha".
     */
    public function forgotPassword(): void
    {
        View::render('admin/auth/forgot_password');
    }

    /**
     * Processa o envio do link de recuperação de senha.
     * Gera token seguro, salva hash no banco, envia email com token raw.
     * Usa mensagem genérica para evitar enumeração de emails.
     */
    public function enviarLinkReset(): void
    {
        $email = trim($_POST['email_usuario'] ?? '');

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Redirect::redirecionarComMensagem('/backend/forgot-password', 'error', 'Informe um email válido.');
            return;
        }

        // Mensagem genérica (anti-enumeração) — sempre a mesma independente de o email existir
        $mensagemSucesso = 'Se o email informado estiver cadastrado, você receberá um link de recuperação em breve.';

        try {
            $usuarios = $this->usuarioModel->buscarUsuariosPorEMail($email);

            if (count($usuarios) === 1) {
                // Gera token criptograficamente seguro
                $tokenRaw = bin2hex(random_bytes(32));
                $tokenHash = hash('sha256', $tokenRaw);
                $expiraEm = date('Y-m-d H:i:s', strtotime('+1 hour'));

                // Salva o HASH do token no banco (não o token raw)
                $this->usuarioModel->salvarTokenReset($email, $tokenHash, $expiraEm);

                // Envia o token RAW por email (o link conterá o token original)
                try {
                    $this->notificacaoEmail->esqueciASenha($email, $tokenRaw);
                }
                catch (\Throwable $eEmail) {
                    error_log("AVISO: Falha ao enviar email de reset para $email: " . $eEmail->getMessage());
                }
            }
        // Se não encontrou o email, não faz nada mas retorna mesma mensagem

        }
        catch (\Throwable $e) {
            error_log("ERRO em enviarLinkReset: " . $e->getMessage());
        }

        Redirect::redirecionarComMensagem('/backend/forgot-password', 'success', $mensagemSucesso);
    }

    /**
     * Renderiza o formulário de redefinição de senha.
     * Valida o token antes de exibir o formulário.
     */
    public function resetPassword(): void
    {
        $token = $_GET['token'] ?? '';

        if (empty($token)) {
            Redirect::redirecionarComMensagem('/backend/forgot-password', 'error', 'Token inválido ou ausente.');
            return;
        }

        // Valida o hash do token no banco
        $tokenHash = hash('sha256', $token);
        $resetData = $this->usuarioModel->validarTokenReset($tokenHash);

        if (!$resetData) {
            Redirect::redirecionarComMensagem('/backend/forgot-password', 'error', 'Token inválido ou expirado. Solicite um novo link.');
            return;
        }

        View::render('admin/auth/reset_password', ['token' => $token]);
    }

    /**
     * Processa a redefinição de senha.
     * Valida token, verifica senhas, atualiza no banco e invalida o token.
     */
    public function processarResetPassword(): void
    {
        $token = $_POST['token'] ?? '';
        $senhaNova = $_POST['senha_nova'] ?? '';
        $senhaConfirm = $_POST['senha_confirm'] ?? '';

        if (empty($token)) {
            Redirect::redirecionarComMensagem('/backend/forgot-password', 'error', 'Token inválido.');
            return;
        }

        // Validação de senha
        if (empty($senhaNova) || strlen($senhaNova) < 6) {
            Redirect::redirecionarComMensagem(
                '/backend/redefinir-senha?token=' . urlencode($token),
                'error',
                'A senha deve ter pelo menos 6 caracteres.'
            );
            return;
        }

        if ($senhaNova !== $senhaConfirm) {
            Redirect::redirecionarComMensagem(
                '/backend/redefinir-senha?token=' . urlencode($token),
                'error',
                'As senhas não conferem.'
            );
            return;
        }

        // Valida o hash do token
        $tokenHash = hash('sha256', $token);
        $resetData = $this->usuarioModel->validarTokenReset($tokenHash);

        if (!$resetData) {
            Redirect::redirecionarComMensagem('/backend/forgot-password', 'error', 'Token inválido ou expirado. Solicite um novo link.');
            return;
        }

        // Busca o usuário pelo email do token
        $usuarios = $this->usuarioModel->buscarUsuariosPorEMail($resetData['email']);

        if (count($usuarios) !== 1) {
            Redirect::redirecionarComMensagem('/backend/forgot-password', 'error', 'Erro ao localizar a conta.');
            return;
        }

        $usuario = $usuarios[0];

        // Atualiza a senha
        $atualizado = $this->usuarioModel->atualizarSenha((int)$usuario['id_usuario'], $senhaNova);

        if ($atualizado) {
            // Invalida todos os tokens deste email
            $this->usuarioModel->deletarTokensReset($resetData['email']);
            Redirect::redirecionarComMensagem('/backend/login', 'success', 'Senha redefinida com sucesso! Faça o login com sua nova senha.');
        }
        else {
            Redirect::redirecionarComMensagem('/backend/forgot-password', 'error', 'Erro ao redefinir a senha. Tente novamente.');
        }
    }
}
