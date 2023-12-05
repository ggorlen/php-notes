<?php
// basic app in a single file for demonstration purposes

//session_start();
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

$db = new SQLite3('app.db');
$db->enableExceptions(true);
$db->exec('
  CREATE TABLE IF NOT EXISTS notes
  (id INTEGER PRIMARY KEY, text TEXT NOT NULL)
');

if (isset($_POST['_method']) && $_POST['_method'] === 'delete' && isset($_POST['id'])) {
    $stmt = $db->prepare('DELETE FROM notes WHERE id = :id;');
    $stmt->bindValue(':id', $_POST['id']);
    $result = $stmt->execute();
}
if (isset($_POST['_method']) && $_POST['_method'] === 'post' && isset($_POST['note'])) {
    $stmt = $db->prepare('INSERT INTO notes (text) VALUES (:text);');
    $stmt->bindValue(':text', $_POST['note']);
    $result = $stmt->execute();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title>PHP Notes</title>
  <meta charset="utf-8">
  <meta name="description" content="PHP Notes">
  <meta name="color-scheme" content="dark light">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
<script>
if (window.history.replaceState) {
  window.history.replaceState(null, null, window.location.href);
}
</script>

<h1>Notes</h1>

<form method="POST">
  <label>
    New note
    <input name="note">
  </label>
  <input name="_method" type="hidden" value="post" />
  <input type="submit">
</form>
<ul>
<?php

$query = $db->query('SELECT * FROM notes ORDER BY id DESC;');
$rows = 0;
while ($row = $query->fetchArray(/*SQLITE3_ASSOC*/1)) {
    $rows++;
    echo '
      <li>
        <span>'.$row['text'].'</span>
        <form method="POST">
          <input name="_method" type="hidden" value="delete" />
          <input name="id" hidden value="'.$row['id'].'">
          <input type="submit" value="X">
        </form>
      </li>';
}

?>
</ul>
<div>
  <small>
    <?= $rows ?> notes
  </small>
</div>
<hr>
<h3>Debug</h3>
<pre>
<?php

if (error_get_last()) {
    echo '$error_get_last: ';
    var_export(error_get_last());
}
if (isset($php_response_header)) {
    echo '$php_response_header: ';
    var_export($php_response_header);
}
echo '$GLOBALS: ';
$_SERVER; // not a noop, needed to populate $GLOBALS
var_export($GLOBALS);
print_r($path);
?>
</pre>
</body>
</html>
