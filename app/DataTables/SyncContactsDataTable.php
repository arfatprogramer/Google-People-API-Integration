<?php

namespace App\DataTables;

use App\Models\client;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class SyncContactsDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder<client> $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->filterColumn('syncStatus', function($query, $keyword) {
                $query->where('syncStatus', 'like', "%{$keyword}%");
            })

            // ->addColumn('Check', function($row) {
            //     return '<input type="checkbox" class="h-4 w-4 rounded border-gray-300">';
            // })
            ->setRowClass('rowHoverClass ')
            ->addColumn('action', function($row) {
                    $disable="";
                if ($row->syncStatus=="Synced") {
                    $disable="Disabled";
                }
                return '<div class=" flex gap-1"><button '.$disable.' data-sync-id='.$row->id.' class="singleSyncContact hover:text-blue-600 cursor-pointer inline-flex items-center justify-center gap-2 whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg]:size-4 [&amp;_svg]:shrink-0 hover:bg-accent hover:text-accent-foreground rounded-md h-8 w-8 p-0"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-refresh-cw h-4 w-4">
                    <path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"></path>
                    <path d="M21 3v5h-5"></path>
                    <path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"></path>
                    <path d="M8 16H3v5"></path>
                </svg></button><a href='.route('client.edit',$row->id).'> <button class="hover:text-blue-600 cursor-pointer inline-flex items-center justify-center gap-2 whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg]:size-4 [&amp;_svg]:shrink-0 hover:bg-accent hover:text-accent-foreground rounded-md h-8 w-8 p-0"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 6V18M18 12H6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg></button></a></div>';
            })
            ->addColumn('updated_at',function($row){
                // $date = time() - strtotime($row->lastSync??Time());
                $row->lastSync==null ? ($date = 0) : ($date = time() - strtotime($row->lastSync));
                $min = round($date / 60); // seconds to minutes
                $hours = round($date / 3600); // seconds to hours
                $days = round($date / 86400); // seconds to days

                if ( $min >= 0 && $min < 60) {
                    return "$min Minutes Ago";
                } elseif ($hours> 0 && $hours < 24) {
                    return "$hours Hours Ago";
                } elseif ($days >= 1 ) {
                    return "$days Days Ago";
                } else {
                    return "Never";
                }

            })
            ->addColumn('syncStatus',function($row){
                $data='';
                if ($row->syncStatus=='Synced') {

                    $data= "<div class='inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 gap-1 bg-green-50 text-green-700' data-v0-t='badge'>$row->syncStatus</div>";
                }elseif ($row->syncStatus=='Pending'){

                    $data= "<div class='inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 gap-1 bg-yellow-50 text-yellow-700' data-v0-t='badge'>$row->syncStatus</div>";
                }
                else{
                    $data= "<div class='inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 gap-1 bg-gray-50 text-gray-700' data-v0-t='badge'>$row->syncStatus</div>";
                }
                return $data;
            })
            ->rawColumns(['action','syncStatus']);
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<client>
     */
    public function query(client $model): QueryBuilder
    {
        return $model->newQuery()->orderBy('updated_at', 'desc');
    }


    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('synccontacts-table')
            ->columns($this->getColumns())
            ->minifiedAjax(route('sync.contacts.data'))
            ->orderBy(4)
            ->selectStyleSingle()
            ->parameters([
                // 'dom' => 'Bfrt<bottom ip>',
                'dom' => '<"synccontacts-table-search flex justify-between items-center mb-4"fB>lrt<"synccontacts-table flex justify-between items-center mt-4"ip>',
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
                Button::make('reset'),
                Button::make('reload')
            ]);
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            // Column::computed('Check')
            //     ->exportable(false)
            //     ->printable(false)
            //     ->width(10)
            //     ->addClass('text-center'),

            Column::make('firstName')->title('First Name')
            ->addClass('px-4 py-3 font-medium'),
            Column::make('email')->title('Email'),
            Column::make('number')->title('Phone Number'),
            Column::make('syncStatus')->title('Sync Status')->searchable(true),
            Column::make('updated_at')->title('Last Sync'),

            Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->width(60)
                ->addClass('text-center'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'SyncContacts_' . date('YmdHis');
    }
}
