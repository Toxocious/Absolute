<?php
  require_once '../core/required/layout_top.php';
?>

<style>
  .panel.content canvas,
  .panel.content img
  {
    border: 2px solid #000;
    border-color: #000;
    border-radius: 6px;
  }
</style>

<div class='panel content'>
  <div class='head'>Image Processor</div>
  <div class='body' style='padding: 5px;'>
    <div class='description'>
      This tool was created to process and extract all unique colors from a given image.
    </div>

    <div class='flex'>
      <table class='border-gradient' style='max-height: 184px; width: 500px;'>
        <tbody>
          <tr>
            <td colspan='2' style='width: 50%;'>
              <b>Selected Image</b>:<br />
              <img height='96' width='96' id='image_element' />
            </td>
            <td colspan='2' style='width: 50%;'>
              <b>Processing Canvas</b>:<br />
              <canvas height='96' width='96' id='image_canvas'></canvas>
            </td>
          </tr>
          <tr>
            <td colspan='2' style='padding: 10px;'>
              <input type='file' id='select_image' />
            </td>
            <td colspan='2' style='padding: 10px;'>
              <input type='button' id='process_image' value='Process Image' />
            </td>
          </tr>
        </tbody>
      </table>

      <table class='border-gradient' style='width: 350px;'>
        <tbody id='found_colors'>
          <tr>
            <td colspan='4' style='padding: 5px;'>Select an image to begin</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script type='text/javascript'>
  const Image_Element = document.getElementById('image_element');
  const Canvas = document.getElementById('image_canvas');
  let Canvas_Context;

  const ResetProcessing = (Canvas, Canvas_Context) =>
  {
    document.getElementById('found_colors').innerHTML = '';
    Canvas_Context.clearRect(0, 0, Canvas.width, Canvas.height);
  }

  const AppendMessage = (Message) =>
  {
    if ( typeof Message === 'undefined' || Message == '' )
      return;

    document.getElementById('found_colors').innerHTML += `<tr>${Message}</tr>`;
  }

  const HexFromRGBA = (Color) =>
  {
    const Output = [
      Color.r.toString(16),
      Color.g.toString(16),
      Color.b.toString(16),
      Math.round(Color.a * 255).toString(16).substring(0, 2)
    ];

    Output.forEach((Part, i) =>
    {
      if ( Part.length === 1 )
        Output[i] = `0${Part}`;
    });

    return '#' + Output.join('');
  }

  const ProcessImage = (File_Selector_File = null) =>
  {
    if ( typeof Canvas !== 'undefined' && typeof Canvas_Context !== 'undefined' )
    {
      ResetProcessing(Canvas, Canvas_Context);
    }

    Canvas_Context = Canvas.getContext('2d');
    Canvas_Context.drawImage(Image_Element, 0, 0, 96, 96, 0, 0, 96, 96);

    const Canvas_Data = Canvas_Context.getImageData(0, 0, Canvas.width, Canvas.height);
    const Canvas_Pixels = Canvas_Data.data;

    if ( Canvas_Pixels.length < 1 )
    {
      AppendMessage('<td colspan="4" style="color: red;"><b>Failed to process selected image</b></td>');
      return;
    }

    const Pixels = [];
    for ( let i = 0; i < Canvas_Pixels.length; i += 4 )
    {
      const red = Canvas_Pixels[i];
      const green = Canvas_Pixels[i + 1];
      const blue = Canvas_Pixels[i + 2];
      const alpha = Canvas_Pixels[i + 3];

      let Found_Pixel = false;
      for ( const Pixel in Pixels )
      {
        let Cur = Pixels[Pixel];

        if ( Cur.r == red && Cur.g == green && Cur.b == blue && Cur.a == alpha )
        {
          Found_Pixel = true;
          Cur.amt++;
          break;
        }
      }

      if ( !Found_Pixel )
      {
        Pixels.push({ r: red, g: green, b: blue, a: alpha, amt: 1 });
      }
    }

    Pixels.sort(function(a, b)
    {
      if (a.amt > b.amt)
        return -1;
      if (a.amt <  b.amt)
        return 1;

      return 0;
    });

    document.getElementById('found_colors').innerHTML = '';

    AppendMessage(`
      <td><b>Color</b></td>
      <td><b>Hits</b></td>
      <td><b>Hex #</b></td>
      <td><b>RGBA</b></td>
    `);

    for (const Pixel in Pixels )
    {
      const Color = Pixels[Pixel];

      AppendMessage(`
        <td>
          <div style='margin: 0 auto; height: 20px; width: 20px; background: rgba(${Color.r}, ${Color.g}, ${Color.b}, ${Color.a});'></div>
        </td>
        <td>${Color.amt}</td>
        <td>${HexFromRGBA(Color)}</td>
        <td>${Color.r}, ${Color.g}, ${Color.b}, ${Color.a}</td>
      `);
    }

    Image_Loaded = false;
  }

  (function()
  {
    const Process_Image = document.getElementById('process_image');
    const File_Selector = document.getElementById('select_image');
    const Image_Element = document.getElementById('image_element');

    let Image_Loaded = false;

    File_Selector.addEventListener('change', (event) => {
      if ( typeof Canvas !== 'undefined' && typeof Canvas_Context !== 'undefined' )
      {
        ResetProcessing(Canvas, Canvas_Context);
      }

      if (File_Selector.files && File_Selector.files[0])
      {
        const reader = new FileReader();

        reader.onload = function(e)
        {
          Image_Element.setAttribute('src', e.target.result);
          Image_Loaded = true;
        }

        reader.readAsDataURL(File_Selector.files[0])
      }
    });

    Process_Image.addEventListener('click', () => {
      Process_Image.disabled = true;

      ProcessImage();

      Process_Image.disabled = false;
    });
  })();
</script>

<?php
  require_once '../core/required/layout_bottom.php';
