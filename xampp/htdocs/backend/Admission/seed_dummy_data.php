<?php
// Script to seed dummy data for users and user_application tables

$servername = "localhost";
$username = "root";
$password_db = "Omamam@010101";
$dbname = "admission";

$conn = new mysqli($servername, $username, $password_db, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Insert admin user
$adminName = "Admin User";
$adminEmail = "admin@example.com";
$adminPassword = password_hash("adminpassword", PASSWORD_DEFAULT);
$isAdmin = 1;

$stmt_admin = $conn->prepare("INSERT INTO Users (name, email, password, is_admin) VALUES (?, ?, ?, ?)");
$stmt_admin->bind_param("sssi", $adminName, $adminEmail, $adminPassword, $isAdmin);
if (!$stmt_admin->execute()) {
    echo "Error inserting admin user: " . $stmt_admin->error . "\n";
} else {
    echo "Inserted admin user\n";
}
$stmt_admin->close();

function randomDate($start_date, $end_date) {
    $min = strtotime($start_date);
    $max = strtotime($end_date);
    $val = rand($min, $max);
    return date('Y-m-d', $val);
}

$firstNames = ['James', 'Mary', 'John', 'Patricia', 'Robert', 'Jennifer', 'Michael', 'Linda', 'William', 'Elizabeth', 'David', 'Barbara', 'Richard', 'Susan', 'Joseph', 'Jessica', 'Thomas', 'Sarah', 'Charles', 'Karen', 'Christopher', 'Nancy', 'Daniel', 'Lisa', 'Matthew', 'Betty', 'Anthony', 'Margaret', 'Mark', 'Sandra', 'Donald', 'Ashley', 'Steven', 'Kimberly', 'Paul', 'Emily', 'Andrew', 'Donna', 'Joshua', 'Michelle'];
$lastNames = ['Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Garcia', 'Miller', 'Davis', 'Rodriguez', 'Martinez', 'Hernandez', 'Lopez', 'Gonzalez', 'Wilson', 'Anderson', 'Thomas', 'Taylor', 'Moore', 'Jackson', 'Martin', 'Lee', 'Perez', 'Thompson', 'White', 'Harris', 'Sanchez', 'Clark', 'Ramirez', 'Lewis', 'Robinson', 'Walker', 'Young', 'Allen', 'King', 'Wright', 'Scott', 'Torres', 'Nguyen', 'Hill', 'Flores'];

$usedNames = [];

for ($i = 1; $i <= 40; $i++) {
    do {
        $firstName = $firstNames[array_rand($firstNames)];
        $lastName = $lastNames[array_rand($lastNames)];
        $name = $firstName . ' ' . $lastName;
    } while (in_array($name, $usedNames));
    $usedNames[] = $name;

    $email = strtolower(str_replace(' ', '.', $name) . $i . '@example.com');
    $password = password_hash("password$i", PASSWORD_DEFAULT);

    // Insert user
    $stmt_user = $conn->prepare("INSERT INTO Users (name, email, password, is_admin) VALUES (?, ?, ?, 0)");
    $stmt_user->bind_param("sss", $name, $email, $password);
    if (!$stmt_user->execute()) {
        echo "Error inserting user $name: " . $stmt_user->error . "\n";
        continue;
    }
    $user_id = $stmt_user->insert_id;
    $stmt_user->close();

    // Prepare dummy application data
    $dob = randomDate('1990-01-01', '2005-12-31');
    $courses = ['Computer Science', 'Business Administration', 'Engineering', 'Psychology', 'Education'];
    $course = $courses[array_rand($courses)];
    $contact = "0917" . str_pad(rand(1000000, 9999999), 7, "0", STR_PAD_LEFT);
    $address = "123 Main St, City $i";
    $guardianName = "Guardian $i";
    $guardianRelation = "Parent";
    $guardianAddress = "123 Guardian St, City $i";

    // Insert application
    $stmt_app = $conn->prepare("INSERT INTO user_application (user_id, name, dob, course, contact, email, address, guardianName, guardianRelation, guardianAddress) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt_app->bind_param("isssssssss", $user_id, $name, $dob, $course, $contact, $email, $address, $guardianName, $guardianRelation, $guardianAddress);
    if (!$stmt_app->execute()) {
        echo "Error inserting application for user $name: " . $stmt_app->error . "\n";
    } else {
        echo "Inserted user and application for $name\n";
    }
    $stmt_app->close();
}

$conn->close();
?>
