<div>
                <p class="text-gray-500">Last Sync (Seconds Ago)</p>
                <p class="font-semibold text-blue-700">
                    <!-- @if($client->last_synced_at)
                    {{ now()->diffInSeconds($client->last_synced_at) }} seconds ago
                    @else
                    N/A
                    @endif -->
                </p>
            </div>

            <div>
                <p class="text-gray-500">Last Sync Date</p>
                <p class="font-medium text-gray-900">
                    {{ $client->last_synced_at ? $client->last_synced_at->format('Y-m-d H:i:s') : 'Not Synced' }}
                </p>
            </div>




            
