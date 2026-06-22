                    <div class="table-container">
                        <table class="text-sm" id="performanceTable">

                            <thead>
                                <tr class="text-md text-white">
                                    <th class="px-4 py-3"
                                        style="min-width:300px; background: linear-gradient(to right, #2563eb, #1e40af); color: #fff; border-bottom: 3px solid #3b82f6;">
                                        Programs/Activities/Projects (P/A/Ps)
                                    </th>
                                    <th class="px-4 py-3"
                                        style="min-width:240px; background: linear-gradient(to right, #2563eb, #1e40af); color: #fff; border-bottom: 3px solid #3b82f6;">
                                        Performance Indicators</th>
                                    <th class="px-4 py-3"
                                        style="min-width:180px; background: linear-gradient(to right, #2563eb, #1e40af); color: #fff; border-bottom: 3px solid #3b82f6;">
                                        Office / Unit</th>
                                    <!-- month headers added dynamically -->
                                </tr>
                                <tr class="group-row" id="groupHeaders"></tr>
                            </thead>

                            <tbody class="text-gray-800">
                                @include('admin.continuing.partials.continuing_physical_table_rows')
                            </tbody>
                        </table>
                    </div>
