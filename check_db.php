<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/backend/Core/Env.php';
\Datislopo\Ebook\Core\Env::carregar(__DIR__.'/backend');

require_once __DIR__ . '/backend/Database/Database.php';

try {
    $db = \Datislopo\Ebook\Database\Database::getInstance();
    
    // Hash the existing client user password
    $clientEmail = "ivanildoespiritosanto@gmail.com";
    $plainPasswordClient = "Elshadai7@";
    $hashClient = password_hash($plainPasswordClient, PASSWORD_DEFAULT);
    $stmt1 = $db->prepare("UPDATE tbl_usuario SET senha_usuario = :hash WHERE email_usuario = :email");
    $stmt1->execute(['hash' => $hashClient, 'email' => $clientEmail]);
    
    // Check if an admin exists
    $stmt2 = $db->query("SELECT * FROM tbl_usuario WHERE email_usuario = 'admin@test.com'");
    if (!$stmt2->fetch()) {
        $adminEmail = "admin@test.com";
        $plainPasswordAdmin = "123456";
        $hashAdmin = password_hash($plainPasswordAdmin, PASSWORD_DEFAULT);
        
        $stmt3 = $db->prepare("INSERT INTO tbl_usuario (nome_usuario, email_usuario, senha_usuario, tipo_usuario) VALUES ('Admin Teste', :email, :hash, 'Admin')");
        $stmt3->execute(['hash' => $hashAdmin, 'email' => $adminEmail]);
        echo "Admin created successfully.\n";
    } else {
        echo "Admin already exists.\n";
    }
    
    echo "Client password hashed successfully.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
