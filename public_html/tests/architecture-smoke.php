<?php

declare(strict_types=1);

$root = dirname(__DIR__);

function starterRead(string $root, string $path): string
{
    $full = $root . '/' . $path;
    $content = file_get_contents($full);
    if ($content === false) throw new RuntimeException('Missing starter file: ' . $path);
    return $content;
}

function starterMustContain(string $content, string $needle, string $message): void
{
    if (!str_contains($content, $needle)) throw new RuntimeException($message);
}

$services = starterRead($root, 'config/services.yaml');
$security = starterRead($root, 'config/packages/security.yaml');
$sonata = starterRead($root, 'config/packages/sonata_admin.yaml');
$user = starterRead($root, 'src/Entity/User.php');
$userAdmin = starterRead($root, 'src/Admin/UserAdmin.php');
$exampleAdmin = starterRead($root, 'src/Admin/ExampleContentAdmin.php');
$command = starterRead($root, 'src/Command/CreateAdminCommand.php');
$uploader = starterRead($root, 'src/Services/FileUploader.php');
$readme = starterRead(dirname($root), 'README.md');

starterMustContain($services, 'admin.user:', 'User Sonata admin must be registered.');
starterMustContain($services, 'admin.example_content:', 'Example Sonata admin must be registered.');
starterMustContain($security, "path: '^/admin'", 'Admin routes must require ROLE_ADMIN.');
starterMustContain($sonata, 'sonata_admin:', 'Sonata Admin must be configured.');
starterMustContain($user, 'PasswordAuthenticatedUserInterface', 'Starter User must support password login.');
starterMustContain($userAdmin, 'hashPlainPassword', 'User admin must safely hash changed passwords.');
starterMustContain($exampleAdmin, 'CKEditorType::class', 'Example admin must demonstrate rich text.');
starterMustContain($command, "name: 'app:create-admin'", 'Starter must include the first-admin command.');
starterMustContain($uploader, 'getMimeType()', 'Uploader must validate server-inspected MIME types.');
starterMustContain($readme, 'php bin/console make:entity', 'README must explain the feature workflow.');

echo "Architecture starter smoke checks passed.\n";
