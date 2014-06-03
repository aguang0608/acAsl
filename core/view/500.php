<!doctype html>
<html>
  <head>
    <title>acAsl - 500</title>
    <meta charset="utf-8">
    <link rel="stylesheet" href="<?php echo acAsl_PATH ; ?>/static/site.css">
  </head>
  <body>
    <div class="header">
      acAsl
    </div>
    <div class="content">
      <a href="<?php echo acAsl_PATH ; ?>">Back</a>
      there is something wrong!<br>
      <?php if ( $this->arguments ) { ?>
        Debug Info : <br>
        <?php echo $this->arguments ; ?>
      <?php } ?>
    </div>
  </bdoy>
</html>