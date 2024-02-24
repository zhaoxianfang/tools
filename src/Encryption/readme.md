# 加密解密

## Rsa加密解密

```php
// 使用示例（请确保在安全的环境中执行密钥生成操作）
use zxf\Encryption\Rsa;
try {
// 生成密钥对并保存到文件（可选）
$privateKeyFile = './private_key.pem'; // 私钥文件路径（请确保路径安全）
$publicKeyFile  = './public_key.pem'; // 公钥文件路径（通常可以公开）
if (!Rsa::generateKeyPair(2048, true, $privateKeyFile, $publicKeyFile)) {
throw new Exception('Failed to generate key pair or save keys to file.');
}

    // 加载私钥和公钥（如果之前已经保存过密钥到文件）
    // $privateKeyPem = file_get_contents($privateKeyFile);
    // Rsa::loadPrivateKey($privateKeyPem); // 在这个示例中不需要，因为生成密钥对时已经加载了私钥
    // $publicKeyPem = file_get_contents($publicKeyFile);
    // Rsa::loadPublicKey($publicKeyPem); // 在这个示例中不需要，因为生成密钥对时已经加载了公钥

    // 使用公钥加密数据
    $plaintext = 'Hello, RSA!'; // 待加密的数据
    $encrypted = Rsa::encryptWithPublicKey($plaintext,$publicKeyFile);
    if ($encrypted === null) {
        throw new Exception('Encryption failed.');
    }
    echo 'Encrypted: ' . $encrypted . PHP_EOL; // 输出加密后的数据（Base64编码）

    // 使用私钥解密数据（通常在另一个安全的环境中执行）
    $decrypted = Rsa::decryptWithPrivateKey($encrypted,$privateKeyFile); // 注意：解密操作应该在可以安全访问私钥的环境中进行
    if ($decrypted === null) {
        throw new Exception('Decryption failed.');
    }
    echo 'Decrypted: ' . $decrypted . PHP_EOL; // 输出解密后的数据，应该与原始数据相同（'Hello, RSA!'）
} catch (Exception $e) {
    // 处理异常（记录错误日志、显示错误信息或进行其他操作）
    Rsa::handleError('An error occurred: ' . $e->getMessage(), $e); // 这将记录错误并抛出异常，上层代码应该捕获并适当处理它。
}
```