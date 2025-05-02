<?php
// session_start();


require 'vendor/autoload.php'; // Load Composer dependencies
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

class Database
{
    private $conn;

    public function __construct()
    {
        $host = $_ENV['DB_HOST'];
        $db_name = $_ENV['DB_NAME'];
        $username = $_ENV['DB_USER'];
        $password = $_ENV['DB_PASS'];

        $dsn = "mysql:host=$host;dbname=$db_name;charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ];

        try {
            $this->conn = new PDO($dsn, $username, $password, $options);
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            die("Database connection error.");
        }
    }

    public function getConnection()
    {
        return $this->conn;
    }
}

$conn = (new Database())->getConnection();


// ---------------------------
// API Handling (korrekt hantering med event_id och sökning)
// ---------------------------
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['api'], $_GET['event_id'])) {
    session_start();
    header('Content-Type: application/json');

    $event_id = (int)$_GET['event_id'];
    $user_id = $_SESSION['user_id'];

    // Kontrollera att användaren äger eventet
    $eventCheckStmt = $conn->prepare("SELECT id FROM events WHERE id = ? AND user_id = ?");
    $eventCheckStmt->execute([$event_id, $user_id]);
    if (!$eventCheckStmt->fetchColumn()) {
        echo json_encode(["error" => "Unauthorized or invalid event"]);
        exit();
    }

    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 25;
    $offset = ($page - 1) * $limit;
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';

    try {
        // Antal gäster för valt event
        $totalStmt = $conn->prepare("SELECT COUNT(*) as total FROM guests WHERE event_id = :event_id");
        $totalStmt->execute([':event_id' => $event_id]);
        $totalGuests = $totalStmt->fetch(PDO::FETCH_ASSOC)['total'];

        if ($search) {
            // Antal gäster med sökning (filtrerat på namn)
            $filteredStmt = $conn->prepare("SELECT COUNT(*) as filtered FROM guests WHERE event_id = :event_id AND name LIKE :search");
            $filteredStmt->execute([
                ':event_id' => $event_id,
                ':search' => "%$search%"
            ]);
            $filteredTotalGuests = $filteredStmt->fetch(PDO::FETCH_ASSOC)['filtered'];

            // Hämta gäster med sökning
            $stmt = $conn->prepare("SELECT * FROM guests WHERE event_id = :event_id AND name LIKE :search LIMIT :limit OFFSET :offset");
            $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
        } else {
            // Inget sökfilter, filtrerade gäster = totala gäster
            $filteredTotalGuests = $totalGuests;

            // Hämta gäster utan sökning
            $stmt = $conn->prepare("SELECT * FROM guests WHERE event_id = :event_id LIMIT :limit OFFSET :offset");
        }

        // Bind alla parametrar korrekt
        $stmt->bindValue(':event_id', $event_id, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $guests = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            "totalGuests" => $totalGuests,              // Totalt antal gäster för eventet
            "filteredGuests" => $filteredTotalGuests,   // Antal gäster efter eventuell sökning
            "guests" => $guests                         // Gästerna som hämtades
        ]);
        exit();
    } catch (PDOException $e) {
        echo json_encode(["error" => "Database query failed: " . $e->getMessage()]);
        exit();
    }
}




// ---------------------------
// Check-In & Check-Out Update
// ---------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['check_in'])) {
        $guest_id = (int)$_POST['guest_id'];

        try {
            $stmt = $conn->prepare("UPDATE guests SET checked_in = 1 WHERE id = :guest_id");
            $stmt->bindParam(':guest_id', $guest_id, PDO::PARAM_INT);
            $stmt->execute();

            echo json_encode(["message" => "Guest checked in successfully!"]);
            exit();
        } catch (PDOException $e) {
            echo json_encode(["error" => "Error updating record: " . $e->getMessage()]);
            exit();
        }
    }

    if (isset($_POST['check_out'])) {
        $guest_id = (int)$_POST['guest_id'];

        try {
            $stmt = $conn->prepare("UPDATE guests SET checked_in = 0 WHERE id = :guest_id");
            $stmt->bindParam(':guest_id', $guest_id, PDO::PARAM_INT);
            $stmt->execute();

            echo json_encode(["message" => "Guest checked out successfully!"]);
            exit();
        } catch (PDOException $e) {
            echo json_encode(["error" => "Error updating record: " . $e->getMessage()]);
            exit();
        }
    }
}

$title = "TicketMan.se |";