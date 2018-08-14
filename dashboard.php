<?php
  require 'layout_top.php';
?>

<div class='content'>
  <div class='head'>Your Dashboard</div>
  <div class='box'>
    <div class="row" style='padding: 20px'>
      <div class="col-md-3 col-sm-6">
        <div class="progress blue">
          <span class="progress-left">
            <span class="progress-bar"></span>
          </span>
          <span class="progress-right">
            <span class="progress-bar"></span>
          </span>
          <div class="progress-value">
            Trainer Level
            <div style='font-size: 16px; margin-top: -80px;'>6,969</div>
          </div>
        </div>
      </div>

      <div class="col-md-3 col-sm-6">
        <div class="progress pink">
          <span class="progress-left">
            <span class="progress-bar"></span>
          </span>
          <span class="progress-right">
            <span class="progress-bar"></span>
          </span>
          <div class="progress-value">
            Map Level
            <div style='font-size: 16px; margin-top: -80px;'>1,337</div>
          </div>
        </div>
      </div>

      <div class="col-md-3 col-sm-6">
        <div class="progress green">
          <span class="progress-left">
            <span class="progress-bar"></span>
          </span>
          <span class="progress-right">
            <span class="progress-bar"></span>
          </span>
          <div class="progress-value">
            Misc Level 
            <div style='font-size: 16px; margin-top: -80px;'>420</div>
          </div>
        </div>
      </div>

      <div class="col-md-3 col-sm-6">
        <div class="progress yellow">
          <span class="progress-left">
            <span class="progress-bar"></span>
          </span>
          <span class="progress-right">
            <span class="progress-bar"></span>
          </span>
          <div class="progress-value">
            Misc Level
            <div style='font-size: 16px; margin-top: -80px;'>12</div>
          </div>
        </div>
      </div>
    </div>

    <div class='feed'>
      <div class='panel'>
        <div class='panel-heading'>Your Feed</div>
        <div class='panel-body'>
          <div class='feed-box'>
            <div class='avatar'>
              <img class='admin' src='images/Avatars/Sprites/155.png' />
            </div>
            <div class='username'><span class='admin'>Toxocious</span></div>
            <div class='date'>Dec 6th, 2017 ~ 2:38 PM</div>
            <div class='message'>
              Lorem ipsum dolor sit amet, tincidunt mus ac volutpat metus. Tellus feugiat, in imperdiet aliquam posuere phasellus, ac arcu quisque justo, maecenas amet, sapien ut suspendisse ac sit consectetuer.
            </div>
          </div>

          <div class='feed-box'>
            <div class='avatar'>
              <img src='images/Avatars/Sprites/155.png' />
            </div>
            <div class='username'>Toxocious</div>
            <div class='date'>Dec 6th, 2017 ~ 2:39 PM</div>
            <div class='message'>
              Ultricies pulvinar sed mauris quis lacus scelerisque, dolor ligula at feugiat sit pretium nec, congue dignissim id nunc augue dolor, morbi rutrum curabitur.
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php
  require 'layout_bottom.php';
?>