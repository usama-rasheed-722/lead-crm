<?php
// Simple test to verify assignment form submission
session_start();

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h2>Form Submission Test Results</h2>";
    echo "<h3>POST Data Received:</h3>";
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";
    
    echo "<h3>Lead IDs:</h3>";
    if (isset($_POST['lead_ids'])) {
        echo "<pre>";
        print_r($_POST['lead_ids']);
        echo "</pre>";
    } else {
        echo "<p style='color: red;'>No lead_ids found in POST data!</p>";
    }
    
    echo "<h3>Individual Lead ID:</h3>";
    if (isset($_POST['lead_id'])) {
        echo "<p>Lead ID: " . $_POST['lead_id'] . "</p>";
    } else {
        echo "<p style='color: red;'>No lead_id found in POST data!</p>";
    }
    
    echo "<h3>Assigned To:</h3>";
    if (isset($_POST['assigned_to'])) {
        echo "<p>Assigned To: " . $_POST['assigned_to'] . "</p>";
    } else {
        echo "<p style='color: red;'>No assigned_to found in POST data!</p>";
    }
    
    echo "<h3>Comment:</h3>";
    if (isset($_POST['comment'])) {
        echo "<p>Comment: " . htmlspecialchars($_POST['comment']) . "</p>";
    } else {
        echo "<p style='color: red;'>No comment found in POST data!</p>";
    }
    
    echo "<hr>";
    echo "<a href='test_assignment_form.php'>Test Again</a>";
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Assignment Form Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Assignment Form Test</h1>
        
        <h2>Test Individual Assignment</h2>
        <form method="POST" action="test_assignment_form.php">
            <div class="mb-3">
                <label for="lead_id" class="form-label">Lead ID</label>
                <input type="number" class="form-control" id="lead_id" name="lead_id" value="123" required>
            </div>
            <div class="mb-3">
                <label for="assigned_to" class="form-label">Assign To</label>
                <select class="form-select" id="assigned_to" name="assigned_to" required>
                    <option value="">Select User</option>
                    <option value="1">User 1</option>
                    <option value="2">User 2</option>
                    <option value="3">User 3</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="comment" class="form-label">Comment</label>
                <textarea class="form-control" id="comment" name="comment" rows="3">Test comment</textarea>
            </div>
            <button type="submit" class="btn btn-primary">Test Individual Assignment</button>
        </form>
        
        <hr>
        
        <h2>Test Bulk Assignment</h2>
        <form method="POST" action="test_assignment_form.php">
            <div class="mb-3">
                <label class="form-label">Select Leads</label>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="lead_ids[]" value="101" id="lead101">
                    <label class="form-check-label" for="lead101">Lead 101</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="lead_ids[]" value="102" id="lead102">
                    <label class="form-check-label" for="lead102">Lead 102</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="lead_ids[]" value="103" id="lead103">
                    <label class="form-check-label" for="lead103">Lead 103</label>
                </div>
            </div>
            <div class="mb-3">
                <label for="bulk_assigned_to" class="form-label">Assign To</label>
                <select class="form-select" id="bulk_assigned_to" name="assigned_to" required>
                    <option value="">Select User</option>
                    <option value="1">User 1</option>
                    <option value="2">User 2</option>
                    <option value="3">User 3</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="bulk_comment" class="form-label">Comment</label>
                <textarea class="form-control" id="bulk_comment" name="comment" rows="3">Bulk test comment</textarea>
            </div>
            <button type="submit" class="btn btn-primary">Test Bulk Assignment</button>
        </form>
    </div>
</body>
</html>
