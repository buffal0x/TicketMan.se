<?php
require 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// 1. Hämta användarens events
try {
    $stmt = $conn->prepare("SELECT * FROM events WHERE user_id = ? ORDER BY id ASC");
    $stmt->execute([$user_id]);
    $events = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error fetching events: " . $e->getMessage());
}

// 2. Skapa nytt event
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_event'])) {
    $event_name = trim($_POST['event_name']);
    $event_date = trim($_POST['event_date']);
    $event_description = trim($_POST['event_description']);

    if (empty($event_name) || empty($event_date)) {
        die("Event name and date are required.");
    }

    try {
        $stmt = $conn->prepare(
            "INSERT INTO events (user_id, event_name, event_date, event_description) 
             VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([$user_id, $event_name, $event_date, $event_description]);
        header("Location: dashboard.php");
        exit();
    } catch (PDOException $e) {
        die("Error creating event: " . $e->getMessage());
    }
}

// 3. Ta bort event
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_event_id'])) {
    $delete_event_id = (int)$_POST['delete_event_id'];

    try {
        $checkStmt = $conn->prepare("SELECT user_id FROM events WHERE id = ?");
        $checkStmt->execute([$delete_event_id]);
        $owner = $checkStmt->fetchColumn();

        if ($owner == $user_id) {
            $conn->prepare("DELETE FROM events WHERE id = ?")->execute([$delete_event_id]);
            $conn->prepare("DELETE FROM guests WHERE event_id = ?")->execute([$delete_event_id]);
        }
    } catch (PDOException $e) {
        die("Error deleting event: " . $e->getMessage());
    }

    header("Location: dashboard.php");
    exit();
}

// 4. Validera valt event
$selected_event_id = isset($_GET['event_id']) ? (int)$_GET['event_id'] : null;

$guests = [];
$event_valid = false;

if ($selected_event_id) {
    try {
        $stmt = $conn->prepare("SELECT id FROM events WHERE id = ? AND user_id = ?");
        $stmt->execute([$selected_event_id, $user_id]);
        $event_valid = $stmt->fetchColumn();

        if (!$event_valid) {
            header("Location: dashboard.php");
            exit();
        }
    } catch (PDOException $e) {
        die("Error validating event: " . $e->getMessage());
    }
}

// Lägga till gäster manuellt
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_guest'], $selected_event_id)) {
    $guest_name = trim($_POST['guest_name']);
    $guest_email = trim($_POST['guest_email']);
    $ticket_id = trim($_POST['ticket_id']);

    if (!empty($guest_name) && !empty($guest_email) && !empty($ticket_id)) {
        try {
            $stmt = $conn->prepare("INSERT INTO guests (event_id, name, email, ticket_id) VALUES (?, ?, ?, ?)");
            $stmt->execute([$selected_event_id, $guest_name, $guest_email, $ticket_id]);

            header("Location: dashboard.php?event_id=" . $selected_event_id);
            exit();
        } catch (PDOException $e) {
            die("Error adding guest: " . $e->getMessage());
        }
    } else {
        die("Alla fält måste fyllas i.");
    }
}

use PhpOffice\PhpSpreadsheet\IOFactory;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_excel'], $_FILES['excel_file'], $selected_event_id)) {
    if ($_FILES['excel_file']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['excel_file']['tmp_name'];

        try {
            $spreadsheet = IOFactory::load($fileTmpPath);
            $sheetData = $spreadsheet->getActiveSheet()->toArray();

            foreach ($sheetData as $index => $row) {
                if ($index == 0 || empty($row[0])) continue; // Hoppa över header eller tom rad

                $name = trim($row[0]);
                $email = trim($row[1]);
                $ticket_id = trim($row[2]);

                if (!empty($name) && !empty($email) && !empty($ticket_id)) {
                    $stmt = $conn->prepare("INSERT INTO guests (event_id, name, email, ticket_id) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$selected_event_id, $name, $email, $ticket_id]);
                }
            }

            header("Location: dashboard.php?event_id=" . $selected_event_id);
            exit();
        } catch (Exception $e) {
            die("Error processing Excel file: " . $e->getMessage());
        }
    } else {
        die("Fel vid uppladdning av filen.");
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<title><?php echo $title; ?> Dashboard </title>
<?php include "head.php"; ?>

<body class="bg-gray-900 w-9/12 text-center mx-auto">
    <?php include "banner.php"; ?>

    <div class="mx-auto">

        <!-- Display Events -->
        <div class="bg-gray-800 p-6 shadow-lg rounded-lg text-white mx-auto">
            <h2 class="text-2xl font-bold">Your Events</h2>
            <?php if (empty($events)): ?>
                <p>No events found. Create one above!</p>
            <?php else: ?>
                <ul class="space-y-3">
                    <?php foreach ($events as $event): ?>
                        <li class="bg-gray-700 p-4 rounded">
                            <div class="flex justify-between">
                                <div>
                                    <h3 class="font-bold"><?php echo htmlspecialchars($event['event_name']); ?></h3>
                                    <p><?php echo htmlspecialchars($event['event_date']); ?></p>
                                </div>
                                <div>
                                    <a href="dashboard.php?event_id=<?php echo $event['id']; ?>" class="bg-purple-500 text-white px-3 py-1 rounded hover:bg-purple-600">Manage Guests</a>
                                    <form method="POST" class="inline">
                                        <input type="hidden" name="delete_event_id" value="<?php echo $event['id']; ?>">
                                        <button class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600">Delete</button>
                                    </form>
                                </div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>


        <!-- Guests for selected event -->
        <?php if ($selected_event_id && $event_valid): ?>
            <div id="event-details" class="bg-white p-6 shadow-lg rounded-lg mx-auto">
                <h2 class="text-2xl font-bold mb-4">Guests for Event #<?php echo $selected_event_id; ?></h2>

                <table class="w-full bg-white shadow rounded">
                    <thead class="bg-gray-200">
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Ticket ID</th>
                        </tr>
                    </thead>
                    <tbody id="guest-list"></tbody>
                </table>
            </div>

            <script>
                function fetchGuests() {
                    fetch(`config.php?api=true&event_id=<?php echo $selected_event_id; ?>`)
                        .then(res => res.json())
                        .then(data => {
                            let html = '';
                            data.guests.forEach(guest => {
                                html += `<tr><td>${guest.name}</td><td>${guest.email}</td><td>${guest.ticket_id}</td></tr>`;
                            });
                            document.getElementById('guest-list').innerHTML = html;
                        })
                        .catch(e => console.error("Error:", e));
                }
                document.addEventListener("DOMContentLoaded", fetchGuests);
            </script>
        <?php endif; ?>
    </div>

    <?php include "footer.php"; ?>

</body>

</html>