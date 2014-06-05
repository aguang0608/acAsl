<!doctype html>
<html>
  <head>
    <title>acAsl - Install</title>
    <meta charset="utf-8">
    <link rel="stylesheet" href="<?php echo acAsl_PATH ; ?>/static/site.css">
  </head>
  <body>
    <div class="header">
      acAsl - Installer
    </div>
    <div class="content">
      <?php if( $this->arguments ) { ?>
        <?php if ( $this->arguments[ "success" ] ) { ?>
          <a href="<?php echo acAsl_PATH ; ?>">Start</a>
        <?php } else { ?>
          <a href="<?php echo acAsl_PATH ; ?>">Back</a>
          <?php echo $this->arguments["message"] ; ?>
        <?php } ?>
      <?php } else { ?>
        <form method="post">
          <input name="host" placeholder="host" type="text">
          <input name="user" placeholder="user" type="text">
          <input name="pswd" placeholder="pswd" type="text">
          <input name="db" placeholder="db" type="text">
          <input name="admin" placeholder="admin" type="text">
          <button type="submit">install</button>
        </form>
      <?php } ?>
    </div>
  </bdoy>
</html>