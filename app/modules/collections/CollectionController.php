<?php

require_once __DIR__ . '/CollectionModel.php';

class CollectionController
{
    private CollectionModel $model;
    private int $userId;
    private string $token;

    public function __construct()
    {

        if (!Auth::check()) {
            header('Location: index.php?module=auth&action=login');
            exit;
        }
        if (!Auth::isActive()) {
            Auth::logout();
            header('Location: index.php?module=auth&action=login');
            exit;
        }
        $this->userId = (int) Auth::id();

        $database = Database::getInstance();

        if ($database instanceof PDO) {
            $db = $database;
        } elseif (method_exists($database, 'getPdo')) {
            $db = $database->getPdo();
        } elseif (method_exists($database, 'getConnection')) {
            $db = $database->getConnection();
        } else {
            throw new RuntimeException('Database chưa cung cấp kết nối PDO.');
        }

        $this->model = new CollectionModel($db);

        if (empty($_SESSION['tv4_token'])) {
            $_SESSION['tv4_token'] = bin2hex(random_bytes(16));
        }
        $this->token = $_SESSION['tv4_token'];
    }

    public function index(): void { $this->handle('index'); }
    public function create(): void { $this->handle('create'); }
    public function store(): void { $this->handle('store'); }
    public function show(): void { $this->handle('show'); }
    public function edit(): void { $this->handle('edit'); }
    public function update(): void { $this->handle('update'); }
    public function delete(): void { $this->handle('delete'); }
    public function addDocument(): void { $this->handle('addDocument'); }
    public function removeDocument(): void { $this->handle('removeDocument'); }
    public function favorites(): void { $this->handle('favorites'); }
    public function toggleFavorite(): void { $this->handle('toggleFavorite'); }

    public function handle(string $action): void
    {
        switch ($action) {
            case 'index':
                $this->render('index', ['collections' => $this->model->all($this->userId)]);
                break;
            case 'create':
                $this->render('create');
                break;
            case 'store':
                $this->checkPost();
                [$name, $description] = $this->formData();
                $this->model->create($name, $description, $this->userId);
                $this->go('index');
                break;
            case 'show':
                $collection = $this->ownedCollection();
                $this->render('show', [
                    'collection' => $collection,
                    'documents' => $this->model->documents((int) $collection['id'], $this->userId),
                    'available' => $this->model->addableDocuments($this->userId, (int) $collection['id'])
                ]);
                break;
            case 'edit':
                $this->render('edit', ['collection' => $this->ownedCollection()]);
                break;
            case 'update':
                $this->checkPost();
                $collection = $this->ownedCollection(true);
                [$name, $description] = $this->formData();
                $this->model->update((int) $collection['id'], $name, $description, $this->userId);
                $this->go('index');
                break;
            case 'delete':
                $this->checkPost();
                $collection = $this->ownedCollection(true);
                $this->model->delete((int) $collection['id'], $this->userId);
                $this->go('index');
                break;
            case 'addDocument':
                $this->checkPost();
                $collection = $this->ownedCollection(true);
                $documentId = (int) ($_POST['document_id'] ?? 0);
                if (!$this->model->accessibleDocument($documentId, $this->userId)) {
                    $this->fail('Tài liệu không tồn tại hoặc bạn không có quyền truy cập.');
                }
                $this->model->addDocument((int) $collection['id'], $documentId);
                if (($_POST['return_to'] ?? '') === 'community') {
                    redirect('?module=community&action=show&id=' . $documentId, 'Đã lưu tài liệu vào bộ sưu tập "' . $collection['name'] . '"!', 'success');
                }
                $this->go('show', (int) $collection['id']);
                break;
            case 'removeDocument':
                $this->checkPost();
                $collection = $this->ownedCollection(true);
                $this->model->removeDocument((int) $collection['id'], (int) ($_POST['document_id'] ?? 0));
                $this->go('show', (int) $collection['id']);
                break;
            case 'favorites':
                $this->render('favorites', [
                    'favorites' => $this->model->favorites($this->userId),
                    'available' => $this->model->addableFavorites($this->userId)
                ]);
                break;
            case 'toggleFavorite':
                $this->checkPost();
                $documentId = (int) ($_POST['document_id'] ?? 0);

                if (!$this->model->isFavorite($this->userId, $documentId)
                    && !$this->model->accessibleDocument($documentId, $this->userId)) {
                    $this->fail('Không thể yêu thích tài liệu này.');
                }
                $this->model->toggleFavorite($this->userId, $documentId);
                if (($_POST['return_to'] ?? '') === 'community') {
                    redirect('?module=community&action=show&id=' . $documentId, 'Đã cập nhật trạng thái yêu thích!', 'success');
                }
                $this->go('favorites');
                break;
            default:
                $this->fail('Trang không tồn tại.', 404);
        }
    }

    private function ownedCollection(bool $post = false): array
    {
        $id = (int) (($post ? $_POST : $_GET)['id'] ?? 0);
        $collection = $this->model->find($id, $this->userId);
        if (!$collection) {
            $this->fail('Không tìm thấy bộ sưu tập của bạn.', 404);
        }
        return $collection;
    }

    private function formData(): array
    {
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        if ($name === '' || preg_match_all('/./u', $name) > 150) {
            $this->fail('Tên bộ sưu tập phải từ 1 đến 150 ký tự.');
        }
        return [$name, $description];
    }

    private function checkPost(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->fail('Thao tác này phải dùng form POST.', 405);
        }
        if (!hash_equals($this->token, (string) ($_POST['tv4_token'] ?? ''))) {
            $this->fail('Phiên làm việc không hợp lệ. Vui lòng tải lại trang.', 403);
        }
    }

    private function render(string $page, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        $tv4Token = $this->token;
        $tv4UserId = $this->userId;
        $layout = __DIR__ . '/../../views/layouts/';
        require $layout . 'header.php';
        require __DIR__ . '/views/' . $page . '.php';
        require $layout . 'footer.php';
    }

    private function go(string $action, int $id = 0): void
    {
        $link = 'index.php?module=collections&action=' . $action;
        if ($id > 0) $link .= '&id=' . $id;
        header('Location: ' . $link);
        exit;
    }

    private function fail(string $message, int $status = 400): void
    {
        http_response_code($status);
        exit(htmlspecialchars($message, ENT_QUOTES, 'UTF-8'));
    }
}
class CollectionsController extends CollectionController
{
}
