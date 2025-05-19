<?php

namespace App\DataTables;

use App\Models\clientContatSyncHistory;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class clietsSyncedHistoryDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder<clietsSyncedHistory> $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('status',function($data){
                $tag='';
                if ($data->error==0) {
                    $tag='<div class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 gap-1 bg-green-50 text-green-700" data-v0-t="badge"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-circle-check-big h-3 w-3"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><path d="m9 11 3 3L22 4"></path></svg>Success</div>';
                }else{
                    $tag='<div class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 gap-1 bg-yellow-50 text-yellow-700" data-v0-t="badge"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-circle-alert h-3 w-3"><circle cx="12" cy="12" r="10"></circle><line x1="12" x2="12" y1="8" y2="12"></line><line x1="12" x2="12.01" y1="16" y2="16"></line></svg>Warning</div>';
                }

                return $tag;
            })

            ->setRowClass('rowHoverClass ')
            ->addColumn('updated_at',function($data){
                $date = Carbon::parse($data->created_at)->format('F j, Y');
                $time = Carbon::parse($data->created_at)->format('g:i A');

                return "<p>$date</p><p class='text-[12px] text-slate-600'>$time</p>";
            })
            ->rawColumns(['status','updated_at']);
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<clietsSyncedHistory>
     */
    public function query(clientContatSyncHistory $model): QueryBuilder
    {
        return $model->orderBy('created_at','desc')->newQuery();
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('clietssyncedhistory-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax(route('sync.history.data'))
                    ->orderBy(0)
                    ->selectStyleSingle()
                    ->parameters([
                        'dom' => '<"clietssyncedhistory-search flex justify-between items-center mb-4"B>lrt<"clietssyncedhistory-table flex justify-between items-center mt-4"ip>',
                        'language' => [
                            'search' => '',
                            'searchPlaceholder' => 'Search...',
                            'lengthMenu' => 'Show _MENU_ entries',
                            'paginate' => [
                                'previous' => 'Previous',
                                'next' => 'Next',
                            ],
                        ],
                    ])
                    ->buttons([
                        Button::make('excel'),
                        Button::make('csv'),
                        Button::make('pdf'),
                        Button::make('print'),
                    ]);
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [

            Column::make('updated_at')->title('Date & Time'),
            Column::computed('status')->title('Status')
            ->searchable(false),
            Column::make('created')->title('Added'),
            Column::make('createdAtGoogle')->title("Added on Google"),
            Column::make('updated')->title("Updated"),
            Column::make('updatedAtGoogle')->title("Updated on Google"),
            Column::make('deleted')->title('Deleted'),
            Column::make('error')->title('Errors'),


        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'clietsSyncedHistory_' . date('YmdHis');
    }
}
