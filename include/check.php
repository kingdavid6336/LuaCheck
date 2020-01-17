<?php


$TotalRisk    = 0;
$TotalFounds  = 0;
$BreakCurrent = false;

?>
<center>
    <h1>This Addon has an Risk level of:</h1>
</center>
<?php



$Highest = 0;
$Treffer = array();
if (isset($_FILES['datei']) && !empty($_POST['lelelrs'])) {
    if (!$_FILES['datei']['type'] == "zip" or $_FILES["datei"]["size"] > $Config['Max_Allowed_Bytes_Per_Upload']) {
        echo '<meta http-equiv="refresh" content="0; URL=' . $_SERVER['PHP_SELF'] . '">';
        die();
    }


    $zip = zip_open($_FILES['datei']['tmp_name']);

    if ($zip) {


        while ($zip_entry = zip_read($zip)) {

            $FName = explode(".", zip_entry_name($zip_entry));
            $FName = $FName[1];
            if ($FName == $Config['Search_For_The_File'])
                if (zip_entry_open($zip, $zip_entry)) {

                    $Line = 0;
                    $file = explode("\n", zip_entry_read($zip_entry));
                    foreach ($file as $t) {
                        $line = $Line++;
                        foreach ($SearchFor as $SearchThis) {

                            //if(!preg_match("/\/\//",$t, $treffer))
                            if (preg_match($SearchThis, $t, $treffer)) {

                                foreach ($Whitelisted as $IgnoreThis) {
                                    if (preg_match($IgnoreThis, zip_entry_name($zip_entry))) {
                                        $BreakCurrent = true;
                                    }
                                }

                                if ($BreakCurrent) {
                                    continue;
                                }



                                $Treffer[] = array(
                                    'Found' => $t,
                                    'Path' => zip_entry_name($zip_entry),
                                    'Type' => $SearchFor[$SearchThis]['Type'],
                                    'Desc' => $SearchFor[$SearchThis]['Desc'],
                                    'Risk' => $SearchFor[$SearchThis]['Risk'],
                                    'Line' => $Line
                                );

                                $TotalFounds++;
                                $TotalRisk = $TotalRisk + $SearchFor[$SearchThis]['Risk'];
                                if ($SearchFor[$SearchThis]['Risk'] > $Highest)
                                    $Highest = $SearchFor[$SearchThis]['Risk'];
                            }
                        }
                    }



                    zip_entry_close($zip_entry);
                }
        }
    }
    unlink($_FILES['datei']['tmp_name']);

?>
    <pre id="Advanced" style="display:none;">
<?php
    foreach ($Treffer as $L) {
        echo <<<EOD
   Found: {$L["type"]} - {$L["Path"]}:{$L["Line"]} - <b>({$L['Desc']})</b> Risk:<font color='#b3002d'><b>{$L['Risk']}</b></font>
    &#9;&#9;<font color='#424251'>{$L['Found']}</font>
EOD;
    }

?></pre>

    <h1>
        <center>
            average: <b><?php echo round((($TotalRisk / $TotalFounds) * 2), 0) / 2; ?></b><br>
            highest level <b><?php echo $Highest; ?></b>
        </center>
    </h1><br>
    <center>
        Use it at your own risk!<br>
        <a href="#" onclick="toggle_visibility('Advanced');">Toggle Advanced Log</a>
    </center>
<?
    zip_close($zip);
} else {
    header("Location: /?p=home");
}
