<?php
$path = isset($_GET['path']) ? $_GET['path'] : '.';
$files = scandir($path);
unset($files[0]);
if ($path === '.') {
    unset($files[1]);
}

// Patikrinama ar failas egzistuoja ir ar didesnis nei 0 bits
if (isset($_FILES['failas']) && $_FILES['failas']['size'] > 0) {
  if ($_FILES['failas']['size'] > 400000) {
      echo 'Failo dydis yra per didelis';
  } else {
//pagrindine direktorija
    $currentDirectory = './';
//tikrinama ar linke esanti direktorija egzistuoja ir, jei taip, pridedama  tiksli direktorija, kur failas bus ikeltas
    if(!empty($_GET['path'])){
      $currentDirectory .= rtrim($_GET['path'], '/') . '/'; //rtrim pašalina visus galinius kablelius ir nereikalingus bruksnelius 
    }

    //pats failo pridejimas
          move_uploaded_file($_FILES['failas']['tmp_name'], $currentDirectory . $_FILES['failas']['name']);
          echo 'Failas sekmingai ikeltas';
          //duomenu atnaujinimas
          header('Location: ?path=' . $path);
      }
  }

  
  if (isset($_POST['createItem'])) {
    if (isset($_POST['createType'])) {
        header('Location: ?path=' . $path); 
        //folderi kurimas
        //directory gaunama is inputo value
        if ($_POST['createType'] === 'directory') {
            $filePath = $path . '/' . $_POST['itemName'];
            // kuriamas naujas folderis tam tikroj direktorijoj
            if (!mkdir($filePath)) {
                echo 'Klaida kuriant direktorija';
            } else {
                echo 'Direktorija sekmingai sukurta';
            }
            //failo kurimas
            //file pareina is inputo value
        } elseif ($_POST['createType'] === 'file') {
            $filePath = $path . '/' . $_POST['itemName'];
            //failo sukūrimui naudojama touch
            if (!touch($filePath)) {
                echo 'Klaida kuriant faila';
            } else {
                echo 'Failas sekmingai sukurtas';
            }
        }
    } else {
        echo 'Pasirinkite failo tipa';
    }
}




if (isset($_GET['action']) && $_GET['action'] === "edit" && isset($_GET['file'])) {
  $editFile = $_GET['file']; 
  $form = '<form method="POST" class="input-group my-2" style="width: 50%" "><label>Rename file:</label></div><input type="text" name="newName" class="form-control" value="' . $editFile . '"> <input type="hidden" name="renameItem" value="' . $editFile . '"><div class="buttons mt-3"><button type="submit" class="btn btn-small btn-primary">Rename</button></form>';
} else {
  $form = "";
}


function remove_recursively($path) {
  $files = scandir($path);

  for($i = 2; $i < count($files); $i++) {
      $target = $path . '/' . $files[$i];

      if(is_dir($target)) {
          remove_recursively($target);
      } else {
          unlink($target);
      }
  }

  rmdir($path);
}

if (isset($_GET['action']) && $_GET['action'] === "delete" && isset($_GET['file'])) {
  $deleteFile = $path . '/' . $_GET['file'];
//pagrindiniu failu apsauga
      if ($_GET['file'] !== 'index.php' AND $_GET['file'] !== 'style.css' && file_exists($deleteFile)) {
      if (is_dir($deleteFile)){
        remove_recursively($deleteFile);
         }
         else{
          //istrynimas su unlink
            unlink($deleteFile);
         }
   
      header('Location: ?path=' . $path); 
   }
   else{
    echo 'Pagrindinio failo negalima ištrinti';
  }
 
  } 



if (isset($_GET['action']) && $_GET['action'] === "deleteMultiple" && isset($_POST['checkputs'])) {
  
  
  foreach ($_POST['checkputs'] as $selectedFile) {
    $deleteFile = $path . '/' . $selectedFile;
  
    if ($selectedFile !== 'index.php' && $selectedFile !== 'style.css') {
      if (is_dir($deleteFile)){
     remove_recursively($deleteFile);
      }
      else{
         unlink($deleteFile);
      }
     
     header('Location: ?path=' . $path);
  }
    else{
      echo 'Pagrindinio failo negalima ištrinti';
    }
  

}



}


if (isset($_POST['newName'])) {
  $newFileName = $_POST['newName'];
  $oldFileName = $_POST['renameItem'];
  $oldFilePath = $path . '/' . $oldFileName;
  $newFilePath = $path . '/' . $newFileName;


  if($oldFileName !== 'index.php' && $oldFileName !== 'style.css'){
  if (rename($oldFilePath, $newFilePath)) {
      echo 'Failas pervadintas sėkmingai';
      header('Location: ?path=' . $path);
  } 
  
  else {
      echo 'Klaida pervadinant failą';
  }
 }
 else{
  echo 'Pagrindinis failas negali būti pervadintas';
 }
}


//zipinimas
function addFilesRecursively($archive, $path) {
  $files = scandir($path);
  unset($files[0]);
  unset($files[1]);

  foreach ($files as $file) {
    $dir = $path . '/' . $file;
      if (is_dir($dir)) {
          $archive->addEmptyDir($dir);
          addFilesRecursively($archive, $dir);
      } else {
          $archive->addFile($dir, $dir);
      }
  }
}

if (isset($_GET['action']) && $_GET['action'] === 'zip' && isset($_GET['file'])) {
  $zippedFolder = $_GET['file'];
  $newZip = $zippedFolder . '.zip';

  $archive = new ZipArchive;
  $archive->open($newZip, ZipArchive::CREATE);
    $archive->addEmptyDir($zippedFolder);
      addFilesRecursively($archive, $zippedFolder);
      
      //pabaigiamas zipinimas
      $archive->close();
      header('Location: ?path=' . $path);
      
  
}


//failo atsisiuntimas
if (isset($_GET['action']) and ($_GET['action']) === "download") {
  $file = $_GET['file'];
  header("Content-Description: File Transfer");
  header("Content-Type: application/octet-stream");
  header("Content-Disposition: attachment; filename=\"" . basename($file) . "\"");
  readfile($file);
  exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File manager</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
    <header>
    <nav class="navbar navbar-expand-lg justify-content-between">
  <div class="container-fluid">
    <div class="logo"><a class="navbar-brand" href="#">H3K DEMO</a>
    <a class="navbar-brand" href=<?= "." ?>><i class="bi bi-house-door-fill"></i></a></div>
    
    <div class="collapse navbar-collapse" id="navbarSupportedContent">

    <div class="input-group">
  <input type="text" class="form-control" aria-label="Text input with dropdown button">
  <button class="btn btn-outline-secondary dropdown-toggle d-flex align-items-center" type="button" data-bs-toggle="dropdown" aria-expanded="false"></button>

</div>
    
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link upld" aria-current="page" href="#"><i class="bi bi-cloud-arrow-up-fill ms-1 me-1"></i>Upload</a>
        </li>
        <li class="nav-item">
          <a class="nav-link newItem" href="#"><i class="bi bi-plus-square me-1"></i>New Item</a>
        </li>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#"><i class="bi bi-gear-fill me-1"></i>Settings</a>
        </li>
      </ul>
     
    </div>
  </div>
</nav>
    </header>
    <div class="container">
 
    <form method="POST" action="?action=deleteMultiple&path=<?= $path ?>">

<button class="btn btn-primary selectAll mt-3">Select all</button>
<button class="btn btn-primary mt-3" name="deleteButton">Delete</button>

    <table class="table table-striped mt-3">   
      <thead>
  
                    <tr>
                        <th ></th>
                        <th scope="col">Name</th>
                        <th scope="col">Size</th>
                        <th scope="col">Modified</th>
                        <th scope="col">Actions</th>
                    </tr>
                    
                </thead> <tbody>
   
                    <?php
             
                    foreach ($files as $file) {
                      if($file !== "index.php" && $file !== "style.css"){
                        //tikrinant ar tai failas ar folderis, reikia visa kelia nurodyt, kitu atveju subdirektorijose neskaiciuos faily dydziu
                        $filePath = $path . '/' . $file;
                        //pathinfo visai info, is ten bus imamamas parametras extension
                        $fileExt = pathinfo($file);
                        $allIcons = [
                          'jpg' => 'bi bi-image',
                          'png' => 'bi bi-image-alt',
                          'php' => 'bi bi-filetype-php',
                          'pdf' => 'bi bi-file-pdf',
                          'doc' => 'bi bi-file-earmark-word',
                          'docx' => 'bi bi-file-earmark-word',
                          'txt' => 'bi bi-body-text',
                          'mp3' => 'bi bi-music-note-beamed',
                          'mp4' => 'bi bi-film',
                          'avi' => 'bi bi-film',
                          'html' => 'bi bi-filetype-html',
                          'css' => 'bi bi-filetype-css',
                          'js' => 'bi bi-filetype-js',
                          'zip' => 'bi bi-file-earmark-zip'
                        ];

                        if (is_dir($filePath)) {
                          $icon = 'bi bi-folder';
                        }
                        else if (array_key_exists('extension', $fileExt) && isset($allIcons[$fileExt['extension']])) {
                          $icon = $allIcons[$fileExt['extension']];
                        }
                       
                          else{
                            $icon = 'bi bi-file-earmark ';
                          }
                $fileWithIcon ='<i class="' . $icon . '"></i> ' . $file;
            
                    ?>
                    
                        <tr>
                            <th scope="row"><input class="form-check-input" type="checkbox" name="checkputs[]" value="<?= $file ?>" id="flexCheckDefault"></th>
                            
                            <td>
                              <!-- dirname grazins tevinio folderio pavadinima -->
                              <!-- Jei $file yra '..', tai nuoroda ves i tevini folderi, naudojant dirname($path) funkcija (vienu lygiu auksciau)
                       kitu atveju, jei $file nėra '..', tai direktorija bus dabartinis kelias $path, pridedant $file su / -->
                     
                       <a href="?path=<?= $file === '..' ? dirname($path) : ($file === '..' ? '' : $path . '/' . $file) ?>">
                       <!-- jei failas, leis atsisiusti  -->
                       <?= $file === '..' ? '<i class="bi bi-arrow-left-circle"></i> ' : (is_file($filePath) ? "<a href='?action=download&file=$filePath'>$fileWithIcon</a>" : $fileWithIcon) ?>


                </a>
              <!-- edit forma -->
                <?=(isset($_GET['file']) and $file === $_GET['file']) ? $form : '';?>
            </td>
          
                   <td><?=$file !== '..' ? (is_dir($filePath) ? 'Folder' : round((filesize($filePath) / 1000),2) . ' KB') : ' '; ?></td>
                            <td><?= $file !== '..' ? date('Y-m-d H:i', filemtime($filePath)): ' '; ?></td>
                            <td>
    <?php
    if ($file !== '..') {
        echo '<a href="?action=edit&file=' . $file . '&path=' . $path . '"><i class="bi bi-pencil-square"></i></a>' .
             '<a href="?action=delete&file=' . $file . '&path=' . $path . '"><i class="bi bi-trash-fill ms-1"></i></a>';
             if (is_dir($filePath)) {
            echo '<a href="?action=zip&file=' . $file . '&path=' . $path . '"><i class="bi bi-file-earmark-zip ms-1"></i></a>';
        }
    }

    ?>
</td>
                        </tr>

                        <?php
                       
                       }
                      }
                    ?> 
                                       

         </tbody></table></form>
         <form class="upload" method="POST" ></form>
         <form class="create" method="POST"></form>
    </div>
    <script>
const upload = document.querySelector(".upload");
const create = document.querySelector(".create");
const btn = document.querySelector(".newItem");
const btn2 = document.querySelector(".upld");

btn.addEventListener('click', (e) => {
  e.preventDefault();
  create.innerHTML = '<label for="itemName" class="form-label">File or directory name:</label>' +
  '<input type="text" class="form-control" name="itemName" id="itemName">' +
  '<div class="mt-2 mb-3 form-check">' +
  '<input type="radio" class="form-check-input" id="createDirectory" name="createType" value="directory">' +
  '<label class="form-check-label" for="createDirectory">Folder</label>' +
  '</div>' +
  '<div class="mb-2 form-check">' +
  '<input type="radio" class="form-check-input" id="createFile" name="createType" value="file">' +
  '<label class="form-check-label" for="createFile">File</label>' +
  '</div>' +
  '<div class="buttons">' +
  '<button class="btn btn-primary" type="submit" name="createItem">Create</button>' +
  '<button class="hide ms-2 btn btn-primary">Hide</button>' + 
  '</div>'

});
btn2.addEventListener('click', (e) => {
  e.preventDefault();
  upload.innerHTML = ' <input type="file" class="form-control" name="failas"><div class="buttons mt-3 mb-3"><button class="btn btn-primary">Upload</button> <button class=" hide btn btn-primary ms-2">Hide</button></div>'
});


document.addEventListener('click', (e) => {
  if (e.target.classList.contains('hide')) {
    e.preventDefault();
    const form = e.target.closest('form');
    form.innerHTML = '';
  }
});

const selectAll = document.querySelector('.selectAll')
selectAll.addEventListener('click', (e) =>{
  e.preventDefault();
 const ckeckboxes =document.querySelectorAll('.form-check-input').forEach(el =>{
  el.checked = !el.checked;
 })
  
})

    </script>
</body>
</html>