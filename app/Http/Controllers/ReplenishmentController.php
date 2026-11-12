<?php

namespace App\Http\Controllers;

use App\Services\ReplenishmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Throwable;

class ReplenishmentController extends Controller
{
    public function __construct(private readonly ReplenishmentService $replenishment) {}

    public function store(): RedirectResponse
    {
        try {
            $result = $this->replenishment->createDraftOrders();
        } catch (ValidationException $exception) {
            return back()->with('warning', $exception->errors()['replenishment'][0] ?? 'No reorder drafts were created.');
        } catch (Throwable $exception) {
            report($exception);
            return back()->with('error', 'Suggested purchase orders could not be created. Please try again.');
        }

        if ($result['lines'] === 0) {
            return back()->with('info', 'Matching auto-restock drafts already exist. Review them before ordering; they were left unchanged.');
        }

        $message = 'Created '.$result['orders'].' draft order(s), added '.$result['lines'].' suggested line(s), and reused '.$result['reused'].' existing draft(s). Review them before ordering.';

        return to_route('purchase-orders.index')->with('success', $message);
    }
}
