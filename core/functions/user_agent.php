<?php
function GetUserAgent()
  {
    $User_Agent = $_SERVER['HTTP_USER_AGENT'];
    $Browser = [ 'Unknown', 'Unknown' ];
    $Platform = 'Unknown';
    $Version = null;

    if ( preg_match('/linux/i', $User_Agent) )
      $Platform = 'Linux';
    else if ( preg_match('/macintosh|mac os x/i', $User_Agent) )
      $Platform = 'Mac';
    else if ( preg_match('/windows|win32/i', $User_Agent) )
      $Platform = 'Windows';
    else
      $Platform = 'Unknown: ' . $User_Agent;

    if ( preg_match('/MSIE/i', $User_Agent) && !preg_match('/Opera/i', $User_Agent) )
    {
      $Browser[0] = 'Internet Explorer';
      $Browser[1] = "MSIE";
    }
    elseif ( preg_match('/Firefox/i', $User_Agent) )
    {
      $Browser[0] = 'Mozilla Firefox';
      $Browser[1] = "Firefox";
    }
    elseif ( preg_match('/Chrome/i', $User_Agent) )
    {
      $Browser[0] = 'Google Chrome';
      $Browser[1] = "Chrome";
    }
    elseif ( preg_match('/Safari/i', $User_Agent) )
    {
      $Browser[0] = 'Apple Safari';
      $Browser[1] = "Safari";
    }
    elseif ( preg_match('/Opera/i', $User_Agent) )
    {
      $Browser[0] = 'Opera';
      $Browser[1] = "Opera";
    }
    elseif ( preg_match('/Netscape/i', $User_Agent) )
    {
      $Browser[0] = 'Netscape';
      $Browser[1] = "Netscape";
    }
    else
    {
      $Browser[0] = 'Unknown';
      $Browser[1] = "Unknown";
    }

    $Known = [ 'Version', $Browser[1], 'Other' ];
    $Pattern = '#(?<browser>'.join('|', $Known).')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
    if ( !preg_match_all($Pattern, $User_Agent, $Matches) ) { }

    $Match_Amount = count($Matches['browser']);
    if ( $Match_Amount != 1 )
    {
      if ( strripos($User_Agent, "Version") < strripos($User_Agent, $Browser[1]) )
      {
        $Version = $Matches['version'][0];
      }
    }
    else
    {
      $Version = $Matches['version'][0];
    }

    if ( $Version == null || $Version == "" )
    {
      $Version = "?";
    }

    return [
      'User_Agent' => $User_Agent,
      'Name' => $Browser[1],
      'Version' => $Version,
      'Platform' => $Platform,
      'Pattern' => $Pattern,
    ];
  }
