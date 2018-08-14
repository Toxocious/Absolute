<?php
  require 'layout_top.php';
?>

<script type='text/javascript'>
  function toggleShop(id, type)
  {
    $('#shopPanel .panel-body').html("Loading..");
    $.ajax({
      type: 'post',
      url: 'ajax/ajax_shop.php',
      data: { shop: id, type: type },
      success: function(data)
      {
        $('#shopPanel .panel-body').html(data);
      }
    })
  }
</script>

<div class='content'>
  <div class='head'>Shop Plaza</div>
  <div class='box'>
    <div class='description' style='margin: 0px 0px 5px; width: 100%;'>placeholder description</div>

    <div class='nav'>
      <div onclick='toggleShop(1, "pokemon");'>Pokemon</div>
      <div onclick='toggleShop(1, "items");'>Items</div>
    </div>

    <div class='panel' id='shopPanel' style='border-top-left-radius: 0px;'>
      <div class='panel-heading' style='border-top-left-radius: 0px;'>Shop</div>
      <div class='panel-body' style='padding: 0px 5px 5px;'>
        Shop Content
      </div>
    </div>
  </div>
</div>

<?php
  require 'layout_bottom.php';
?>