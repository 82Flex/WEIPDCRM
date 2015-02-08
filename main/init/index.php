<?php
if (file_exists('../manage/include/connect.inc.php')) {
	header('Location: ./setup-install.php?redirect=true');
} else {
	header('Location: ./setup-config.php?redirect=true');
}
?>