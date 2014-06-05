<!doctype html>
<html>
  <head>
    <title>acAsl - admin_tags</title>
    <meta charset="utf-8">
    <link rel="stylesheet" href="<?php echo acAsl_PATH ; ?>/static/site.css">
  </head>
  <body>
    <div class="header">
      acAsl - admin - tags
    </div>
    <div class="content">
      <div class="admin-tags">
        <?php while( $row = $this->arguments->fetch_array() ) { ?>
          <a href="<?php echo acAsl_PATH?>/?admin/tag/<?php echo $row[ "id" ]?>">
            <?php echo htmlspecialchars( $row["name"] ) ; ?>
          </a>
        <?php } ?>
      </div>
    </div>
  </bdoy>
</html>