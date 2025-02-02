<?php
$servername = "localhost";  // Server name (default: localhost)
$username = "root";         // Default username for MySQL
$password = "";             // Default password (empty in XAMPP/WAMP)
$database = "my_dn";        // Database name

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch HTML from DSE website
$html = file_get_contents('https://www.dsebd.org/');
if ($html === false) {
    die("Error fetching data from DSE website.");
}

// Load HTML into DOMDocument
libxml_use_internal_errors(true);
$dom = new DOMDocument;
$dom->loadHTML($html);
libxml_clear_errors();

// Use XPath to extract stock data
$xPath = new DOMXPath($dom);
$all_hi = $xPath->query('//a[contains(@class,"abhead")]');

foreach ($all_hi as $Item) {
    $text = $Item->textContent;
    $cleanText = preg_replace('/\s+/u', ' ', trim($text));  // Clean extra spaces
    $info = explode(' ', $cleanText);

    if (count($info) >= 4) {
        $name = $info[0];
        $price = $info[1];
        $change = $info[2];
        $percent = $info[3];

        // Check if stock already exists
        $sql = "SELECT * FROM dse_listk WHERE name = ?";
        $stmt = $conn->prepare($sql);
        
        if ($stmt === false) {
            die("SQL prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $result = $stmt->get_result();
        $count = $result->num_rows;

        if ($count >= 1) {
            // Update existing stock data
            $sql1 = "UPDATE dse_listk SET price=?, price_change=?, percent=? WHERE name=?";
            $stmt1 = $conn->prepare($sql1);
            
            if ($stmt1 === false) {
                die("SQL prepare failed: " . $conn->error);
            }
            
            $stmt1->bind_param("ddds", $price, $change, $percent, $name);
            if ($stmt1->execute()) {
                echo "Data UPDATED for $name <br>";
            } else {
                echo "Update Failed: " . $stmt1->error . "<br>";
            }
        } else {
            // Insert new stock data
            $sql2 = "INSERT INTO dse_listk (name, price, price_change, percent) VALUES (?, ?, ?, ?)";
            $stmt2 = $conn->prepare($sql2);
            
            if ($stmt2 === false) {
                die("SQL prepare failed: " . $conn->error);
            }
            
            $stmt2->bind_param("sddd", $name, $price, $change, $percent);
            if ($stmt2->execute()) {
                echo "Data INSERTED for $name .'<br><br>'";
            } else {
                echo "Insert Failed: " . $stmt2->error . "<br>";
            }
        }
    }
}

// Close database connection
$conn->close();
?>
