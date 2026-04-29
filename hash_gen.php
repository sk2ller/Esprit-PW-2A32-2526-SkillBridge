<?php
$password = "admin123";
$hash = password_hash($password, PASSWORD_BCRYPT);
echo "Hash for 'admin123':<br>";
echo "<code>" . htmlspecialchars($hash) . "</code><br><br>";

// Verify it works
if (password_verify($password, $hash)) {
    echo "✓ Hash is correct!";
} else {
    echo "✗ Hash verification failed";
}
?>
