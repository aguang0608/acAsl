<!doctype html>
<html>
  <head>
    <title>acAsl - admin_tag</title>
    <meta charset="utf-8">
    <link rel="stylesheet" href="<?php echo acAsl_PATH ; ?>/static/site.css">
  </head>
  <body>
    <div class="header">
      acAsl - admin - tag
    </div>
    <div class="content">
      <div class="tags">
        <form action="<?php echo acAsl_PATH ; ?>/?admin/tag/<?php echo $this->arguments[ "id" ] ; ?>" method="post">
          <input name="name" type="text" value="<?php echo $this->arguments[ "name" ] ; ?>">
          <button type="submit">edit</button>
        </form>
      </div>
    </div>
  </bdoy>
</html>