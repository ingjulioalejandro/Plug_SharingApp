<?php
include_once('dbcon.php');

$id = mysqli_real_escape_string($conn, $_POST['id']);
$comment = mysqli_real_escape_string($conn, $_POST['comment']);

$query = "UPDATE upload SET comments = '$comment' WHERE id = '$id'";
if ($conn->query($query)) {
    echo 'success';
} else {
    echo 'Error saving comment: ' . $conn->error;
}
?>
