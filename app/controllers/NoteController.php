<?php
// app/controllers/NoteController.php
class NoteController extends Controller {
    protected $noteModel;
    
    public function __construct() {
        parent::__construct();
        $this->noteModel = new NoteModel();
    }
    
    // Add a note to a lead
    public function add() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('index.php?action=leads');
        }
        
        $user = auth_user();
        $leadId = (int)($_POST['lead_id'] ?? 0);
        $type = trim($_POST['type'] ?? 'note');
        $content = trim($_POST['content'] ?? '');
        
        if (!$leadId || !$content) {
            $this->redirect("index.php?action=lead_view&id={$leadId}&error=" . urlencode('Please provide note content'));
        }
        
        try {
            $this->noteModel->add($leadId, $user['id'], $type, $content);
            $this->redirect("index.php?action=lead_view&id={$leadId}&success=" . urlencode('Note added successfully'));
        } catch (Exception $e) {
            $this->redirect("index.php?action=lead_view&id={$leadId}&error=" . urlencode('Failed to add note'));
        }
    }
    
    // Delete a note
    public function delete($noteId) {
        if (!$noteId) {
            $this->redirect('index.php?action=leads');
        }
        
        $user = auth_user();
        
        try {
            $success = $this->noteModel->delete($noteId, $user['id']);
            if ($success) {
                $this->redirect('index.php?action=leads&success=' . urlencode('Note deleted successfully'));
            } else {
                $this->redirect('index.php?action=leads&error=' . urlencode('Failed to delete note or access denied'));
            }
        } catch (Exception $e) {
            $this->redirect('index.php?action=leads&error=' . urlencode('Failed to delete note'));
        }
    }
}
?>