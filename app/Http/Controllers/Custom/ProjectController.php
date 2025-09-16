<?php
namespace App\Http\Controllers\Custom;

use App\Services\ProjectService;
use App\Core\Controller;
use App\Core\Database;
use App\Core\AuthInterface;

class ProjectController extends Controller
{
    private $projectService;

    public function __construct(Database $db, AuthInterface $auth, ProjectService $projectService)
    {
        parent::__construct($db, $auth); // Вызываем родительский конструктор
        $this->projectService = $projectService;
    }

    public function createNewProject()
    {
        $this->requireAuth();

        try {
            $data = $this->request['body'];
            $project = $this->projectService->createNewProject($data);
            return $this->jsonResponse($project, 201);
        } catch (\InvalidArgumentException $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    public function getProjectsBySystem($systemId)
    {
        $this->requireAuth();

        try {
            $projects = $this->projectService->getProjectsBySystem($systemId);
            return $this->jsonResponse($projects);
        } catch (\Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }
}