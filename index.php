<?php
//header('Content-Type: text/html; charset=iso-8859-2');
include 'kerdoiv_class.php';
$kerdoiv = new Kerdoiv();
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-2" />
<script type="text/javascript" src="lib/jquery.min.js"></script>
<script type="text/javascript" src="index.js"></script>
<link rel="stylesheet" href="index.css" />
<title></title>
</head>
<body>
<?php $kerdoiv->doRequest() ?>
</body>
</html>
