<!doctype html>
<html>
  <head>
    <title>acAsl - admin_tags</title>
    <meta charset="utf-8">
    <link rel="stylesheet" href="<?php echo acAsl_PATH ; ?>/static/site.css">
  </head>
  <body>
    <div class="header">
      acAsl - admin - posts
    </div>
    <div class="content">
      <div class="admin-posts">
        <?php while( $row = $this->arguments[ "result" ]->fetch_array() ) { ?>
          <a href="<?php echo acAsl_PATH?>/?admin/post/<?php echo $row[ "id" ]?>">
            <?php echo htmlspecialchars( $row["title"] );?>
          </a>
        <?php } ?>
      </div>
      <?php if ( !( $this->arguments[ "prev" ] === false ) ) { ?>
        <a class="page-prev" href="<?php echo acAsl_PATH ; ?>/?admin/posts/<?php echo $this->arguments[ "prev" ] ; ?>">prev</a>
      <?php } ?>
      <?php if ( !( $this->arguments[ "next" ] === false ) ) { ?>
        <a class="page-next" href="<?php echo acAsl_PATH ; ?>/?admin/posts/<?php echo $this->arguments[ "next" ] ; ?>">next</a>
      <?php } ?>
    </div>
  </bdoy>
</html>