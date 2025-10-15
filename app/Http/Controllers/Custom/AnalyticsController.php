<?php
namespace App\Http\Controllers\Custom;

use App\Services\Core\AnalyticsService;
use App\Core\Controller;
use App\Core\Database;
use App\Core\AuthInterface;

class AnalyticsController extends Controller
{
    private $analyticsService;

    public function __construct(Database $db, AuthInterface $auth, AnalyticsService $analyticsService)
    {
        parent::__construct($db, $auth);
        $this->analyticsService = $analyticsService;
    }

    public function getBranchSummary($branchId)
    {
        $this->requireAuth();

        try {
            $summary = $this->analyticsService->getBranchSummary($branchId);
            return $this->jsonResponse($summary);
        } catch (\Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    public function getEquipmentReport($branchId)
    {
        $this->requireAuth();

        try {
            $report = $this->analyticsService->getEquipmentReport($branchId);
            return $this->jsonResponse($report);
        } catch (\Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }
}