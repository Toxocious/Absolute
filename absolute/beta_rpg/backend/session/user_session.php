<?php
    /**
     * Handle active session logic at the start of page loads.
     *  - Get active user data
     *  - Update active user page info, playtime, and last page active on
     */
    if ( isset($_SESSION['Absolute_Beta']['Logged_In_As']) )
    {
        $Time = time();

        $User_Data = Database::get()
            ->table("users")
            ->where("id", '=', $_SESSION['Absolute_Beta']['Logged_In_As']['ID'])
            ->first();

        if ( !isset($_SESSION['Absolute_Beta']['playtime']) )
        {
            $_SESSION['Absolute_Beta']['playtime'] = $Time;
        }

        $Playtime = $Time - $_SESSION['Absolute_Beta']['playtime'];
        $Playtime = $Playtime > 20 ? 20 : $Playtime;
        $_SESSION['Absolute_Beta']['playtime'] = $Time;

        Database::get()
            ->table('users')
            ->where("id", '=', $User_Data['id'])
            ->update([
                'last_active' => $Time,
                'playtime' => new DBExpr('playtime + :inc', ['inc' => $Playtime])
            ]);


        // try
        // {
        //     $Update_Activity = $PDO->prepare("INSERT INTO `logs` (`Type`, `Page`, `Data`, `User_ID`) VALUES ('pageview', ?, ?, ?)");
        //     $Update_Activity->execute([ $Current_Page['Name'], $Parse_URL['path'], $User_Data['ID'] ]);

        //     $Update_User = $PDO->prepare("UPDATE `users` SET `Last_Active` = ?, `Last_Page` = ?, `Playtime` = `Playtime` + ? WHERE `ID` = ? LIMIT 1");
        //     $Update_User->execute([ $Time, $Current_Page['Name'], $Playtime, $User_Data['ID'] ]);
        // }
        // catch ( PDOException $e )
        // {
        //     HandleError($e);
        // }

        // var_dump($User_Data);
    }
