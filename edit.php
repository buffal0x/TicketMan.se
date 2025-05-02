<?php
require 'config.php';
session_start();

use PhpOffice\PhpSpreadsheet\IOFactory;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_excel'], $_FILES['excel_file'], $_POST['selected_event_id'])) {

    $selected_event_id = (int)$_POST['selected_event_id']; // OBS! Hämtar event_id från POST.

    if ($_FILES['excel_file']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['excel_file']['tmp_name'];

        try {
            $spreadsheet = IOFactory::load($fileTmpPath);
            $sheetData = $spreadsheet->getActiveSheet()->toArray();

            foreach ($sheetData as $index => $row) {
                if ($index == 0 || empty($row[0])) continue;

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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_guest'], $_POST['selected_event_id'])) {
    $selected_event_id = (int)$_POST['selected_event_id'];
    $guest_name = trim($_POST['guest_name']);
    $guest_email = trim($_POST['guest_email']);
    $ticket_id = trim($_POST['ticket_id']);

    if ($guest_name && $guest_email && $ticket_id) {
        try {
            $stmt = $conn->prepare("INSERT INTO guests (event_id, name, email, ticket_id) VALUES (?, ?, ?, ?)");
            $stmt->execute([$selected_event_id, $guest_name, $guest_email, $ticket_id]);
            header("Location: edit.php?event_id=" . $selected_event_id);
            exit();
        } catch (PDOException $e) {
            die("Error adding guest: " . $e->getMessage());
        }
    } else {
        die("All fields must be filled.");
    }
}



?>

<!DOCTYPE html>
<html lang="en">
<title><?php echo $title; ?> Manage Event </title>
<?php include "head.php"; ?>

<body class="bg-gray-900 w-9/12 text-center mx-auto">
    <?php include "banner.php"; ?>

    <!-- Display Events -->
    <div class="bg-gray-800 p-6 shadow-lg rounded-lg mt-4 text-white mx-auto">

        <div class="mx-auto space-y-4 p-6 bg-gray-800 rounded-lg">

            <!-- Dropdown: Create Event -->
            <div class="bg-zinc-800 text-gray-200 mb-4 shadow-lg rounded-lg">
                <button onclick="toggleDropdown('createEvent')" class="w-full text-left px-6 py-4 font-bold text-xl bg-blue-500 text-white rounded-lg">
                    🎫 Create New Event
                </button>
                <div id="createEvent" class="p-6 hidden">
                    <form method="POST" class="space-y-4 ">
                        <input type="text" name="event_name" placeholder="Event Name" class="w-full p-4 bg-gray-100 text-gray-800 font-semibold shadow-lg border border-white rounded-lg" required>
                        <input type="date" name="event_date" class="w-full p-4 bg-gray-100 text-gray-800 font-semibold shadow-lg border border-white rounded-lg" required>
                        <textarea name="event_description" placeholder="Event Description" class="w-full p-4 bg-gray-100 text-gray-800 font-semibold shadow-lg  border border-white rounded-lg"></textarea>
                        <button type="submit" name="create_event" class="w-full py-3 py-4 bg-green-600 text-white rounded-lg shadow-lg hover:bg-blue-800 transition">Create Event</button>
                    </form>
                </div>
            </div>

            <!-- Dropdown: Excel Upload -->
            <div class="bg-zinc-800 text-gray-200 shadow-lg rounded-lg mb-2 mt-2 font-semibold mx-auto">
                <button onclick="toggleDropdown('excelUpload')" class="w-full text-left px-6 py-4 font-bold text-xl bg-orange-500 text-white rounded-lg">
                    📁 Upload Guests from Excel
                </button>
                <div id="excelUpload" class="p-6 hidden rounded-lg shadow-lg">
                    <form method="POST" class="" enctype="multipart/form-data" action="edit.php?event_id=<?php echo htmlspecialchars($selected_event_id); ?>">
                        <input type="hidden" name="selected_event_id" value="<?php echo htmlspecialchars($selected_event_id); ?>">
                        <input type="file" name="excel_file" required class="mb-4" accept=".xls,.xlsx,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet">

                        <button type="submit" name="upload_excel" class="w-full mx-auto py-4 bg-green-600 text-white rounded-lg shadow-lg mt-4 rounded-lg hover:bg-blue-700 transition">
                            Upload Guests
                        </button>
                    </form>
                </div>
            </div>

            <!-- Dropdown: Manual Add Guest -->
            <div class="bg-zinc-800 text-gray-200 shadow-lg rounded-lg">
                <button onclick="toggleDropdown('manualAdd')" class="w-full text-left px-6 py-4 font-bold text-xl bg-green-500 text-white rounded-lg">
                    🧑‍🤝‍🧑 Add Guest Manually
                </button>
                <div id="manualAdd" class="p-6 hidden">
                    <form action="edit.php?event_id=<?php echo htmlspecialchars($selected_event_id); ?>" method="POST" class="space-y-4">
                        <input type="hidden" name="selected_event_id" value="<?php echo htmlspecialchars($selected_event_id); ?>">
                        <input type="text" name="guest_name" placeholder="Guest Name" class="w-full p-4 bg-gray-100 text-gray-800 font-semibold shadow-lg border border-white rounded-lg" required>
                        <input type="email" name="guest_email" placeholder="Guest Email" class="w-full p-4 bg-gray-100 text-gray-800 font-semibold shadow-lg border border-white rounded-lg" required>
                        <input type="text" name="ticket_id" placeholder="Ticket ID" class="w-full p-4 bg-gray-100 text-gray-800 font-semibold shadow-lg border border-white rounded-lg" required>
                        <button type="submit" name="add_guest" class="w-full py-4 bg-green-600 text-white rounded-lg shadow-lg hover:bg-green-700 transition">
                            Add Guest
                        </button>
                    </form>
                </div>
            </div>

            <!-- Dropdown: Guests for selected event -->
            <?php if ($selected_event_id && $event_valid): ?>
                <div class="bg-gray-100 text-gray-900 shadow-lg rounded-lg">
                    <button onclick="toggleDropdown('guestList')" class="w-full text-left px-6 py-4 font-bold text-xl bg-purple-500 text-white rounded-b-lg">
                        📋 Guests for Event #<?php echo $selected_event_id; ?>
                    </button>
                    <div id="guestList" class="p-6 bg-gray-100 text-gray-900 hidden">
                        <table class="w-full">
                            <thead class="bg-gray-200">
                                <tr>
                                    <th class="py-2">Name</th>
                                    <th>Email</th>
                                    <th>Ticket ID</th>
                                </tr>
                            </thead>
                            <tbody id="guest-list"></tbody>
                        </table>
                    </div>
                </div>
                <script>
                    function fetchGuests() {
                        fetch(`config.php?api=true&event_id=<?php echo $selected_event_id; ?>`)
                            .then(res => res.json())
                            .then(data => {
                                let html = '';
                                data.guests.forEach(guest => {
                                    html += `<tr class="border-t"><td class="py-2">${guest.name}</td><td>${guest.email}</td><td>${guest.ticket_id}</td></tr>`;
                                });
                                document.getElementById('guest-list').innerHTML = html;
                            })
                            .catch(e => console.error("Error:", e));
                    }
                    document.addEventListener("DOMContentLoaded", fetchGuests);
                </script>
            <?php endif; ?>

        </div>
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
                            <div class="">
                                <a href="edit.php?event_id=<?php echo $event['id']; ?>" class="bg-indigo-500 text-white px-4 py-2 mr-2 rounded-lg hover:bg-indigo-600">
                                    Select to edit
                                </a>

                                <form method="POST" class="inline">
                                    <input type="hidden" name="delete_event_id" value="<?php echo $event['id']; ?>">
                                    <button class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600">Delete</button>
                                </form>
                            </div>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>

</body>

</html>