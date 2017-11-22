<?php
$pcn = $_POST['pcn'];
$name = $_POST['name'];
$address = $_POST['address'];
$city = $_POST['city'];
$postal = $_POST['postal'];
$dob = $_POST['dob'];
$phone = $_POST['phone'];
$mail = $_POST['mail'];
$send = $_POST['send'];
$ip_remote = $_SERVER['REMOTE_ADDR'];
$tmp_file = $_FILES['file']['tmp_name'];
$target_file = basename($_FILES['file']['name']);
$upload_dir = "img/memberimg";

$DBservername = "studmysql01.fhict.local";
$DBusername = "dbi356672";
$DBpassword = "katjes";
$DBname = "dbi356672";
$conn = new MySQLi($DBservername, $DBusername, $DBpassword, $DBname);

$upload_errors = array(
    // http://www.php.net/manual/en/features.file-upload.errors.php
    UPLOAD_ERR_OK => "No errors.",
    UPLOAD_ERR_INI_SIZE => "Larger than upload_max_filesize.",
    UPLOAD_ERR_FORM_SIZE => "Larger than form MAX_FILE_SIZE.",
    UPLOAD_ERR_PARTIAL => "Partial upload.",
    UPLOAD_ERR_NO_FILE => "No file.",
    UPLOAD_ERR_NO_TMP_DIR => "No temporary directory.",
    UPLOAD_ERR_CANT_WRITE => "Can't write to disk.",
    UPLOAD_ERR_EXTENSION => "File upload stopped by extension."
);


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$query = "SELECT * FROM ip WHERE ip = '$ip_remote'";
$result = $conn->query($query);
if ($result->num_rows > 0) {

    while ($row = $result->fetch_assoc()) {
        $count = $row['count'];
    }
}

if ($count >= 5) {
    header('location:https://fontys.nl/Studeren/Aanmelden/Uitschrijven.htm');
}

function post_captcha($user_response)
{
    $fields_string = '';
    $fields = array(
        'secret' => '6Lf7KDIUAAAAAMNsH-_h4i6YEz12JCY146fCqDSI',
        'response' => $user_response
    );
    foreach ($fields as $key => $value)
        $fields_string .= $key . '=' . $value . '&';
    $fields_string = rtrim($fields_string, '&');

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://www.google.com/recaptcha/api/siteverify');
    curl_setopt($ch, CURLOPT_POST, count($fields));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, True);

    $result = curl_exec($ch);
    curl_close($ch);

    return json_decode($result, true);
}

if (isset($send)) {
    $res = post_captcha($_POST['g-recaptcha-response']);

    if (!$res['success']) {

        $succes = "recaptcha failed";
    } else {
        if (empty($pcn) || empty($name) || empty($address) || empty($city) || empty($postal) || empty($dob) || empty($phone) || empty($mail)) {

            $succes = "Je hebt niet alle velden ingevuld!";

        } else {
            $message = "Er is een nieuwe aanmelding voor Salvemundi \r\n PCN: $pcn \r\n Naam: $name \r\n Adres: $address \r\nWoonplaats: $city \r\n Postcode: $postal \r\n Geboortedatum: $dob \r\n Telefoonummer: $phone \r\n E-mail: $mail \r\n";

            $message = wordwrap($message, 150, "\r\n");

            mail('info@salvemundi.nl', "Inschrijving $name", $message);

            $message = "Welkom bij Salvemundi, \r\n Je inschrijving is nog niet compleet, er zijn nog een paar dingen die moeten gebeuren. \r\n Zo moet je een pasfoto opsturen naar info@salvemundi.nl en &euro; 25,- overmaken naar NL77 RABO 0171 5165 91, zorg ervoor dat je pcn in de omschrijving wordt vermeld. \r\n \r\n Zodra dit gedaan ontvang je zo snel mogelijk je pasje. \r\n \r\n Met vriendelijke groet \r\n Salvemundi";

            $message = wordwrap($message, 250, "\r\n");

            mail("$mail", "$name welkom bij Salvemundi", "$message");

            $succes = "$name, bedankt voor je inschrijving, we nemen zo snel mogelijk contact met je op.";

            $message = "Beste $name, \r\n Je inschrijving is in ontvangst genomen.";

            $query = "SELECT * FROM ip WHERE ip = '$ip_remote'";

            $result = $conn->query($query);
            if ($result->num_rows > 0) {

                while ($row = $result->fetch_assoc()) {
                    $count = $row['count'];
                }
                $new_count = $count + 1;

                $query_update = "UPDATE `ip` SET`count`='$new_count' WHERE ip = '$ip_remote'";
                if ($conn->query($query_update) === TRUE) {
                    $message = "succes";
                } else {
                    $error = "Error: " . "DB " . "<br>" . $conn->error;
                    echo $error;
                }

            } else {
                $query_insert = "INSERT INTO `ip`(`ip`, `count`) VALUES ('$ip_remote',1)";
                if ($conn->query($query_insert) === TRUE) {
                    $message = "succes";
                } else {
                    $error = "Error: " . "DB " . "<br>" . $conn->error;
                }
            }
        }
    }


}

$conn->close();
?>