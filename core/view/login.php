<!doctype html>
<html>
  <head>
    <title>acAsl - Login</title>
    <meta charset="utf-8">
    <link rel="stylesheet" href="<?php echo acAsl_PATH ; ?>/static/site.css">
  </head>
  <body>
    <div class="header">
      acAsl - Login
    </div>
    <div class="content">
      <?php if ( $this->arguments ) { ?>
        <?php echo $this->arguments ; ?><br>
        <a href="<?php echo acAsl_PATH?>/?login">Back</a>
      <?php } else { ?>
        <form action="<?php echo acAsl_PATH . "/?login" ;?>" method="post">
          <input name="admin" placeholder="admin" type="text">
          <button type="submit">login</button>
        </form>
      <?php } ?>
    </div>
  </bdoy>
</html>