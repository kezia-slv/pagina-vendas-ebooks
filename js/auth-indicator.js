document.addEventListener('DOMContentLoaded', () => {
  const authMenu = document.getElementById('authMenu');
  if (!authMenu) return;

  // Mostra um carregando sutil
  authMenu.innerHTML = '<div class="auth-loading"></div>';

  fetch('/backend/check-session')
    .then(response => response.json())
    .then(data => {
      if (data && data.authenticated) {
        // Usuário logado
        const user = data.user;
        const initial = user.name.charAt(0).toUpperCase();
        authMenu.innerHTML = `
          <div class="auth-user-info">
            <div class="auth-avatar" title="${user.role}">${initial}</div>
            <span class="auth-name">Olá, ${user.name.split(' ')[0]}</span>
          </div>
          <button id="btnAuthLogout" class="auth-btn" style="color: var(--danger); border: none; background: none; cursor: pointer;" title="Sair">
            <i class="fas fa-sign-out-alt"></i> Sair
          </button>
        `;

        // Inject modal to body if not exists
        if (!document.getElementById('logoutModal')) {
          const modalHtml = `
            <div class="auth-modal-overlay" id="logoutModal">
              <div class="auth-modal">
                <div class="auth-modal-icon">
                  <i class="fas fa-door-open"></i>
                </div>
                <h3>Saindo da conta</h3>
                <p>Tem certeza que deseja sair da sua conta? Você precisará fazer login novamente para acessar seus ebooks.</p>
                <div class="auth-modal-actions">
                  <button class="auth-btn auth-btn-cancel" id="btnCancelLogout">Cancelar</button>
                  <button class="auth-btn auth-btn-confirm" id="btnConfirmLogout">Sim, sair agora</button>
                </div>
              </div>
            </div>
          `;
          document.body.insertAdjacentHTML('beforeend', modalHtml);

          const logoutModal = document.getElementById('logoutModal');
          document.getElementById('btnCancelLogout').addEventListener('click', () => {
            logoutModal.classList.remove('active');
          });
          document.getElementById('btnConfirmLogout').addEventListener('click', () => {
             window.location.href = '/backend/logout';
          });
        }

        document.getElementById('btnAuthLogout').addEventListener('click', () => {
          document.getElementById('logoutModal').classList.add('active');
        });

      } else {
        // Não logado
        authMenu.innerHTML = `
          <a href="/backend/login" class="auth-btn">Entrar</a>
          <a href="/backend/register" class="auth-btn primary">Criar conta</a>
        `;
      }
    })
    .catch(error => {
      console.error('Erro ao verificar sessão:', error);
      // Fallback em caso de erro
      authMenu.innerHTML = `
        <a href="/backend/login" class="auth-btn">Entrar</a>
      `;
    });
});
