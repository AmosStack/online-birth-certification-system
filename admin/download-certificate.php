<?php
require_once 'dompdf/autoload.inc.php';
include('includes/dbconnection.php');
ob_start();

?>
<!DOCTYPE html>
<html>
<head>
	<title>Birth Certificate</title>
<style>
table, th, td {
  border: 1px solid;
}
</style>
</head>
<body>
<h2 align="center">Birth Certificate  Details</h2>

	<?php 

$cid=intval($_GET['cid']);
  $query = $dbh->prepare("SELECT tblapplication.*,tbluser.FirstName,tbluser.LastName,tbluser.MobileNumber,tbluser.Address from tblapplication join tbluser on tblapplication.UserID=tbluser.ID where tblapplication.ApplicationID=:cid");
  $query->bindParam(':cid', $cid, PDO::PARAM_INT);
  $query->execute();
  $results = $query->fetchAll(\PDO::FETCH_ASSOC);

foreach ($results as $row) { ?>
<h3>Application / Certificate Number: <?php  echo obcs_escape($row['ApplicationID']);?></h3>
<table  align="center" border="1" width="100%">

<tr>
    <th width="150">Full Name</th>
    <td width="250"><?php  echo obcs_escape($row['FullName']);?></td>
    <th width="150">Gender</th>
    <td><?php  echo obcs_escape($row['Gender']);?></td>
  </tr>
   <tr>
    <th scope>Date of Birth</th>
    <td><?php  echo obcs_escape($row['DateofBirth']);?></td>
    <th scope>Place of Birth</th>
    <td><?php  echo obcs_escape($row['PlaceofBirth']);?></td>
  </tr>
</table>

  <table  align="center" border="1" width="100%" style="margin-top:3%;">
  <tr>
    <th width="150">Name of Mother</th>
    <td width="250"><?php  echo obcs_escape($row['NameOfMother']);?></td>
       <th width="150">Name of Father</th>
    <td><?php  echo obcs_escape($row['NameofFather']);?></td>

  </tr>
   <tr>
<th scope>Permanent Address of Parents</th>
    <td><?php  echo obcs_escape($row['PermanentAdd']);?></td>
    <th scope>Postal Address Permanent Address of Parents</th>
    <td><?php  echo obcs_escape($row['PostalAdd']);?></td>

  </tr>
   <tr>
        <th scope>Parents Mobile Number</th>
    <td><?php  echo obcs_escape($row['MobileNumber']);?></td>
    <th scope>Parents Email</th>
    <td><?php  echo obcs_escape($row['Email']);?></td>

  </tr>


</table>

<table  align="center" border="1" width="100%" style="margin-top:3%;">
<tr>
	    <th width="150">Certificate Number</th>
    <td><?php  echo obcs_escape($row['ApplicationID']);?></td>
    <th >Apply Date</th>
    <td><?php  echo obcs_escape($row['Dateofapply']);?></td>

  </tr>
   <tr>
    <th width="150">Issued Date</th>
    <td><?php  echo obcs_escape($row['UpdationDate']);?></td>
  </tr>
</table>

<?php } ?>

<p>THIS IS A COMPUTER GENERATED CERTIFICATE. </p>
</body>
</html>
<?php
$html = ob_get_clean();
$dompdf = new Dompdf\Dompdf();
$dompdf->setPaper('A4', 'landscape');
$dompdf->load_html($html);
$dompdf->render();
//For view
//$dompdf->stream("",array("Attachment" => false));
// for download
$dompdf->stream("Birth-Certificate.pdf");
?>