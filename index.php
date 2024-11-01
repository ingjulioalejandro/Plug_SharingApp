<?php
// index.php

$conn = new mysqli('localhost', 'root', '', 'myweb') or die(mysqli_error($conn));
session_start();

if (isset($_POST['submit']) && isset($_FILES['photo'])) {
  $original_name = $_FILES['photo']['name'];
  $size = $_FILES['photo']['size'];
  $type = $_FILES['photo']['type'];
  $temp = $_FILES['photo']['tmp_name'];
  $date = date('Y-m-d H:i:s');
  $user_id = $_SESSION['unique_id'];

  $name = !empty($_POST['custom_name']) ? $_POST['custom_name'] . "." . pathinfo($original_name, PATHINFO_EXTENSION) : $original_name;

  move_uploaded_file($temp, "files/" . $name);

  $query = $conn->query("INSERT INTO upload (name, date, user_id, comments) VALUES ('$name', '$date', '$user_id', '')");

  if ($query) {
    header("location:index.php");
  } else {
    die(mysqli_error($conn));
  }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>PLUG (Sharing Files)</title>
<link href="css/bootstrap.css" rel="stylesheet" type="text/css" media="screen">
<link rel="stylesheet" type="text/css" href="css/DT_bootstrap.css">
<link rel="stylesheet" type="text/css" href="css/font-awesome.css">
<link rel="stylesheet" href="css/bootstrap.min.css">
<link rel="stylesheet" href="font-awesome/css/font-awesome.min.css"/>
<script src="js/jquery.js" type="text/javascript"></script>
<script src="js/bootstrap.js" type="text/javascript"></script>
<script type="text/javascript" charset="utf-8" language="javascript" src="js/jquery.dataTables.js"></script>
<script type="text/javascript" charset="utf-8" language="javascript" src="js/DT_bootstrap.js"></script>
<?php include('dbcon.php'); ?>
<style>

body{
    background-image: url("2089149.jpg");
    background-repeat: no-repeat;
    background-size: cover;
}

.table tr th{
    border:#eee 1px solid;
    position:relative;
    font-size:12px;
    text-transform:uppercase;
}
table tr td{
    border:#eee 1px solid;
    color:#000;
    position:relative;
    font-size:12px;
    text-transform:uppercase;
}

#wb_Form1 {
   background-color: rgba(255,255,255,0.4);
   border: 0px #000 solid;
}

#photo {
   background-color: rgba(255,255,255,0);
   color: #fff;
   font-family:Arial;
   font-size: 20px;
}

#custom_name_container {
    display: none;
}
</style>
</head>
<body>
<div style="text-align:center;color:rgba(0,0,0)">
<font size="+2"><b>PLUG</b></font>
</div>
<table cellpadding="0" cellspacing="0" border="0" class="table table-bordered">
  <tr>
    <td>
      <form enctype="multipart/form-data" action="" id="wb_Form1" name="form" method="post">
      <input type="file" name="photo" id="photo" required="required" onchange="showCustomNameField()">
    </td>
    <td id="custom_name_container">
      <label for="custom_name">File name:</label>
      <input type="text" name="custom_name" id="custom_name">
    </td>
    <td>
      <input type="submit" class="btn btn-danger" style="background-color:rgba(51,51,51);color: white;border: 1px" value="Upload File" name="submit">
      </form>
    </td>
  </tr>
</table>

<div class="col-md-18">
  <div class="container-fluid" style="margin-top:0px;">
    <div class="row">
      <div class="panel panel-default" style="background-color:rgba(255,255,255,0.4)">
        <div class="panel-body">
          <div class="table-responsive">

            <form method="post" action="delete.php">
            <table cellpadding="0" cellspacing="0" border="0" class="table table-condensed" id="example">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>FILE NAME</th>
                  <th>DATE</th>
                  <th>UPLOADED BY</th>
                  <th>COMMENTS</th>
                  <th>DOWNLOAD</th>
                  <th>REMOVE</th>
                </tr>
              </thead>
              <tbody>
              <?php
              $query = $conn->query("
                SELECT upload.*, users.fname, users.lname
                FROM upload
                LEFT JOIN users ON upload.user_id = users.unique_id
                ORDER BY upload.id DESC
              ") or die(mysqli_error($conn));
              
              while($row = mysqli_fetch_assoc($query)){
                $id = $row['id'];
                $name = $row['name'];
                $date = $row['date'];
                $uploaded_by = $row['fname'] . ' ' . $row['lname'];
                $comment = isset($row['comments']) ? $row['comments'] : '';
              ?>
                <tr>
                  <td><?php echo $id; ?></td>
                  <td><?php echo $name; ?></td>
                  <td><?php echo $date; ?></td>
                  <td><?php echo $uploaded_by; ?></td>
                  <td>
                      <span id="comment_<?php echo $id; ?>"><?php echo $comment; ?></span>
                      <input type="text" id="edit_comment_<?php echo $id; ?>" style="display:none;" value="<?php echo $comment; ?>">
                      <button id="editBtn_<?php echo $id; ?>" type="button" onclick="toggleEdit(<?php echo $id; ?>)"><i class="fa fa-pencil"></i></button>
                      <button id="saveBtn_<?php echo $id; ?>" type="button" style="display:none;" onclick="saveComment(<?php echo $id; ?>)"><i class="fa fa-save"></i></button>
                  </td>
                  <td><a href="download.php?filename=<?php echo $name; ?>" title="click to download"><span class="glyphicon glyphicon-paperclip" style="font-size:20px; color:blue"></span></a></td>
                  <td><a href="delete.php?del=<?php echo $id; ?>"><span class="glyphicon glyphicon-trash" style="font-size:20px; color:red"></span></a></td>
                </tr>
              <?php
              }
              ?>
              </tbody>
            </table>
            </form>

            <a href="../PlugApp/login.php" class="btn btn-danger" style="background-color:rgba(51,51,51);color: white;border: 1px">Go To Chat App</a>

          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
    function toggleEdit(id) {
        var commentSpan = document.getElementById('comment_' + id);
        var editInput = document.getElementById('edit_comment_' + id);
        var saveBtn = document.getElementById('saveBtn_' + id);
        var editBtn = document.getElementById('editBtn_' + id);

        if (editInput.style.display === 'none' || editInput.style.display === '') {
            // Mostrar campo de edición y el botón de guardar
            commentSpan.style.display = 'none';
            editInput.style.display = 'inline-block';
            saveBtn.style.display = 'inline-block';
            editBtn.style.display = 'none'; // Ocultar el botón de edición
        } else {
            // Ocultar campo de edición y volver a mostrar el comentario
            commentSpan.style.display = 'inline-block';
            editInput.style.display = 'none';
            saveBtn.style.display = 'none';
            editBtn.style.display = 'inline-block'; // Volver a mostrar el botón de edición
        }
    }

    function saveComment(id) {
        var comment = document.getElementById('edit_comment_' + id).value;

        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'add_comment.php', true);
        xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
        xhr.onreadystatechange = function () {
            if (xhr.readyState == 4 && xhr.status == 200) {
                if (xhr.responseText.trim() === 'success') {
                    // Actualizar el comentario en la vista y cerrar el editor
                    document.getElementById('comment_' + id).innerText = comment;
                    toggleEdit(id); // Cerrar el editor solo cuando se guarde con éxito
                } else {
                    alert('Error saving comment: ' + xhr.responseText);
                }
            }
        };
        xhr.send('id=' + id + '&comment=' + encodeURIComponent(comment));
    }

    // Mostrar el campo para el nombre personalizado del archivo al seleccionar un archivo
    function showCustomNameField() {
        var container = document.getElementById('custom_name_container');
        container.style.display = 'block';

        var fileInput = document.getElementById('photo');
        var fileName = fileInput.files[0].name;

        // Remover la extensión del archivo si lo deseas
        var dotIndex = fileName.lastIndexOf('.');
        var fileNameWithoutExtension = dotIndex != -1 ? fileName.substring(0, dotIndex) : fileName;

        var customNameInput = document.getElementById('custom_name');
        customNameInput.value = fileNameWithoutExtension;
    }
</script>

</body>
</html>
