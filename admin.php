<?php
include 'admin_class.php';
$admin = new Admin();
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-2" />
<script type="text/javascript" src="lib/jquery.min.js"></script>
<script type="text/javascript" src="lib/jquery.crypt.js"></script>
<script type="text/javascript" src="admin.js"></script>
<link rel="stylesheet" href="admin.css" />
<title></title>
</head>
<body>
<?php $admin->doRequest() ?>
</body>
</html>