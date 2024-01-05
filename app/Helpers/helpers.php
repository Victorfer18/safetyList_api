<?php

use Firebase\JWT\JWT;

define('ERROR_SEARCH_NOT_FOUND', 'Não encontrado(a)');
define('ERROR_SEARCH_NOT_FOUND_USER', 'Usuário não encontrado para o uso desta ferramenta');
define('ERROR_INVALID_INPUT', 'Entrada inválida');
define('ERROR_PERMISSION_DENIED', 'Seu usuário não está habilitado para acessar essa ferramenta');
define('ERROR_ALREADY_EXISTS', 'Já existe um registro com essas informações');
define('ERROR_INVALID_CPF', 'Número de CPF inválido');
define('ERROR_INVALID_CNPJ', 'Número de CNPJ inválido');
define('ERROR_INVALID_TELEPHONE', 'Número de telefone inválido');
define('ERROR_EMAIL_NOT_REGISTERED', 'E-mail não cadastrado no sistema');
define('ERROR_PASSWORD_MISMATCH', 'Nova senha e confirmação de senha não são iguais');
define('ERROR_INVALID_PASSWORD', 'Senha atual inválida');
define('ERROR_INVALID_FILE_FORMAT', 'Formato de arquivo inválido');
define('ERROR_FILE_TOO_LARGE', 'O tamanho do arquivo é muito grande');
define('INFO_SUCCESS', 'Operação realizada com sucesso');
define('INFO_WARNING', 'Atenção: esta ação não pode ser desfeita');
define('INFO_EMAIL_SENT', 'E-mail enviado com sucesso');
define('INFO_ACCOUNT_CREATED', 'Conta criada com sucesso. Verifique seu e-mail para ativar sua conta');
define('WARNING_SESSION_EXPIRED', 'Sua sessão expirou. Faça login novamente');
define('WARNING_PASSWORD_WEAK', 'A senha é fraca. Use uma combinação de letras, números e caracteres especiais');
define('CONFIRM_DELETE_USER', 'Tem certeza de que deseja excluir este usuário? Esta ação não pode ser desfeita');
define('ERROR_INVALID_USER_OR_PASSWORD', 'Usuário ou senha inválidos');
define('ERROR_ACCOUNT_INACTIVE', 'Sua conta está inativa. Entre em contato com o suporte para obter assistência');
define('ERROR', 'Algo deu errado. Entre em contato com o suporte para obter assistência');



function uploadFile(object $file, string $filePath)
{
    if ($file !== null && $file instanceof \CodeIgniter\HTTP\Files\UploadedFile) {
        $destinationDirectory = getcwd() . '/uploads/' . $filePath;
        if (!is_dir($destinationDirectory)) {
            mkdir($destinationDirectory, 0755, true);
            $sourceIndexPath = __DIR__ . '/../../public/uploads/index.html';
            $destinationIndexPath = $destinationDirectory . 'index.html';
            copy($sourceIndexPath, $destinationIndexPath);
        }
        $fileName = time() . "." . $file->getExtension();
        $destinationPath = $destinationDirectory . $fileName;
        if (move_uploaded_file($file->getPathname(), $destinationPath)) {
            return $filePath . $fileName;
        }
        return false;
    }
    return false;
}

function removeFile(string $file)
{
    $destinationDirectory = getcwd() . '/uploads/' . $file;
    if (file_exists($destinationDirectory) and is_readable($destinationDirectory)) {
        unlink($destinationDirectory);
    }
}

function fileToURL(string $file, string $subFolder = ""): string
{
    $server = "https://safetylist.safety2u.com.br/public" . $subFolder . "/" . $file;
    return $server;
}


function generateJWT(array $payload, string $key): string
{
    return JWT::encode($payload, $key, "HS256");
}

function formatSecretKey(string $value): string
{
    return substr($value, strlen('hex2bin:'));
}
