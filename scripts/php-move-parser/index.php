<?php
  require_once '../../core/required/layout_top.php';
?>

<div class='panel content'>
  <div class='head'>PHP Move Parser</div>
  <div class='body' style='padding: 5px;'>
    <div id='parse-log'>
      Awaiting JS Parsing
      <br />
    </div>
  </div>
</div>

<script>document.querySelector('#parse-log').innerHTML += '<br />Move JS : Loading';</script>
<script type='text/javascript' src='./showdown-moves.js'></script>
<script>document.querySelector('#parse-log').innerHTML += '<br />Move JS : Loaded';</script>
<script>document.querySelector('#parse-log').innerHTML += '<br /><br />---<br /><br />';</script>

<script type='text/javascript'>
  (function() {

    console.log(`Move Count:`, Object.keys(Moves).length);

    let i = 0;
    let Flagless = 0;
    for ( Move in Moves )
    {
      let Name = Moves[Move].name;
      let Flags = Moves[Move].flags;

      if ( Object.keys(Flags).length > 0 )
      {
        // UpdateDatabase(Name, Flags);
      }
      else
      {
        console.log(Name, 'has no flags.', Flags);
        Flagless++;
      }

      i++;

      // if ( i > 10 )
      //   return;
    }

    console.log(`Flagless Move Count: ${Flagless}`);
    console.log(`Moves w/ Flags Count: ${Object.keys(Moves).length - Flagless}`)
  })();

  function UpdateDatabase(Move_Name, Move_Flags)
  {
    console.log(Move_Name, Move_Flags);

    if
    (
      typeof Move_Name !== 'string' ||
      typeof Move_Flags !== 'object'
    )
    {
      document.querySelector('#parse-log').innerHTML += `<div style='color: red;'>Failed to parse ${Move_Name}</div>`;
      return;
    }

    setTimeout(function() {
      $.ajax({
        url: 'update-database.php',
        type: 'POST',
        data: { Move_Name: Move_Name, Move_Flags: Move_Flags },
        success: function(res)
        {
          document.querySelector('#parse-log').innerHTML += res;
        },
        error: function(res)
        {
          document.querySelector('#parse-log').innerHTML += res;
        }
      });
    }, 100);
  }
</script>

<?php
  require_once '../../core/required/layout_bottom.php';
