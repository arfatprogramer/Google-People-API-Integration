<?php

namespace App\DataTables;

use App\Models\client;
use App\Services\CrmApiServices;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\CollectionDataTable;

class SyncContactsDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder<client> $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): CollectionDataTable
    {
        // $page = request()->get('page', 1);  // Default to page 1 if not set
        // $pageSize = request()->get('pageSize', 10);  // Default to 10 items per page

        // // Optionally get search keyword from the request (if any)
        // $search = request()->get('search', '');

         $res = (new CrmApiServices(session('crm_token')))->getContacts();
            $datas=$res['data']??[];
            // $totalCount = $res['total_count'];
            $contacts=[];
            foreach($datas as $data){
                $tempData=[];
                $tempData['id']=$data['id'];
                $tempData['firstName']=$data['name'];
                $tempData['number']=$data['phone_primary'];
                $tempData['email']=$data['email_primary'];
                $tempData['syncStatus']=$data['sync_status_c'];
                $tempData['lastSync']=$data['last_sync_c'];
                $tempData['updated_at']=$data['updated_at'];
                $contacts[]=$tempData;
            }

        return (new CollectionDataTable(collect($contacts)))
            // ->addIndexColumn()
            // ->filterColumn('syncStatus', function($query, $keyword) {
            //     $query->where('syncStatus', 'like', "%{$keyword}%");
            // })

            // ->addColumn('Check', function($row) {
            //     return '<input type="checkbox" class="h-4 w-4 rounded border-gray-300">';
            // })
            ->addIndexColumn()
            ->addColumn('firstName',function($data){
                return "<td> <a href='https://uat.sanchaycrm.com/contacts/".$data['id']."' target='_blank' rel='noopener noreferrer'>".$data['firstName']??''."</a> </td>";
            })
            ->setRowClass('rowHoverClass ')
            ->setRowId('id')
            ->addColumn('action', function($row) {
                    $disable="";
                    $syncStatus=empty($row['syncStatus'])?'Not Synced':$row['syncStatus'];
                if ($row['syncStatus']=="Synced") {
                    $disable="disabled";
                }
                return '<div class=" flex gap-1"><button '.$disable.' data-sync-id='.$row['id'].' data-sync-status='.$syncStatus.' class="singleSyncContact hover:text-blue-600 cursor-pointer inline-flex items-center justify-center gap-2 whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg]:size-4 [&amp;_svg]:shrink-0 hover:bg-accent hover:text-accent-foreground rounded-md h-8 w-8 p-0"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-refresh-cw h-4 w-4">
                    <path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"></path>
                    <path d="M21 3v5h-5"></path>
                    <path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"></path>
                    <path d="M8 16H3v5"></path>
                </svg></button><a href='.route('client.edit',$row['id']).'> <button class="hover:text-blue-600 cursor-pointer inline-flex items-center justify-center gap-2 whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg]:size-4 [&amp;_svg]:shrink-0 hover:bg-accent hover:text-accent-foreground rounded-md h-8 w-8 p-0"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 6V18M18 12H6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg></button></a></div>';
            })
             ->addColumn('syncStatus',function($row){
                $data='';
                if ($row['syncStatus']=='Synced') {

                    $data= "<div class='inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 gap-1 bg-green-50 text-green-700' data-v0-t='badge'>".$row['syncStatus']."</div>";
                }elseif ($row['syncStatus']=='Pending ' || $row['syncStatus']=='pending'){

                    $data= "<div class='inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 gap-1 bg-yellow-50 text-yellow-700' data-v0-t='badge'>".$row['syncStatus']."</div>";
                }elseif($row['syncStatus']=='Deleted'){
                    $data= "<div class='inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 gap-1 bg-gray-50 text-red-700' data-v0-t='badge'>".$row['syncStatus']."</div>";
                }
                else{
                    // $data= "<div class='inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 gap-1 bg-gray-50 text-gray-700' data-v0-t='badge'>".$row['syncStatus']."</div>";
                    $data= "<div class='inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 gap-1 bg-gray-50 text-gray-700' data-v0-t='badge'>Not Synced</div>";
                }
                return $data;
            })
            ->addColumn('updated_at',function($row){
                if ($row['lastSync']==null) {
                    return "Never";
                }

                $date = time() - strtotime($row['lastSync']);
                $seconds =$date;

                $minutes = floor($date / 60);
                $hours = floor($date / 3600);
                $days = floor($date / 86400);

                if ($seconds < 60) {
                    return "$seconds Seconds Ago";
                } elseif ($minutes < 60) {
                    return "$minutes Minutes Ago";
                } elseif ($hours < 24) {
                    return "$hours Hours Ago";
                } else {
                    return "$days Days Ago";
                }

            })

            ->rawColumns(['Sr','firstName','action','syncStatus']);
            // ->setTotalRecords(10);
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<client>
     */
    public function query(client $model): QueryBuilder
    {
        // return $model->newQuery()->orderBy('updated_at', 'desc');
        return $model->newQuery()->limit(0); // not used anymore because data will be Lodad From API
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
            // ->orderBy(5)
            ->selectStyleSingle()
            ->parameters([
                // 'dom' => 'Bfrt<bottom ip>',
                'dom' => '<"synccontacts-table-search flex justify-between items-center mb-4" fB>lrt<"synccontacts-table flex justify-between items-center mt-4"ip>',
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
            Column::computed('DT_RowIndex')
            ->title('Sr')
            ->width(30)
            ->addClass('text-center'),

            Column::make('firstName')->title('First Name')
            ->addClass('px-4 py-3 font-medium'),
            Column::make('email')->title('Email'),
            Column::make('number')->title('Phone Number'),
            Column::make('syncStatus')->title('Sync Status')->searchable(true),
            Column::make('updated_at')->title('Last Sync')->width(160),

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
