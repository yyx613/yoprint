<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Laravel</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

        <!-- Import Tailwindcss -->
        <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
        {{-- Import Jquery --}}
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    </head>
    <body>

        <div class="h-screen overflow-hidden flex flex-col p-8">
            {{-- Upload file input --}}
            <div class="mb-4">
                <label for="file-ipt" class="cursor-pointer bg-blue-500 p-4 inline-block w-full text-center rounded text-white">
                    Upload File
                </label>
                <input type="file" id="file-ipt" class="hidden" />
            </div>
            {{-- Upload status in table --}}
            <div class="flex-1 border border-gray-300 rounded">
                <table class="w-full">
                    <thead>
                        <tr>
                            <th class="border-b border-gray-300 py-1 px-2 w-1/3">Uploaded Time</th>
                            <th class="border-b border-gray-300 py-1 px-2 w-1/3">File Name</th>
                            <th class="border-b border-gray-300 py-1 px-2 w-1/3">Status</th>
                        </tr>
                    </thead>
                    <tbody id="uploadTableBody">
                        <!-- Upload status rows will be dynamically added here -->
                        @foreach ($histories as $uploadedFile)
                            <tr data-upload-id="{{ $uploadedFile->id }}">
                                <td class="border-b border-gray-300 py-1 px-2 text-center">{{ $uploadedFile->created_at }}</td>
                                <td class="border-b border-gray-300 py-1 px-2 text-center">{{ $uploadedFile->file_name }}</td>
                                <td class="border-b border-gray-300 py-1 px-2 text-center">
                                    @if ($uploadedFile->status === 'completed')
                                        <span class="text-green-500">Completed</span>
                                    @elseif ($uploadedFile->status === 'processing')
                                        <span class="text-blue-500">Processing</span>
                                    @elseif ($uploadedFile->status === 'failed')
                                        <span class="text-red-500">Failed to Process</span>
                                    @else
                                        <span>-</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </body>
    <script>
        const POLLING_INTERVAL = 15000; // 15 seconds

        $('#file-ipt').on('change', function(event) {
            const file = event.target.files[0];
            if (!file) return;

            // Add a new row to the table with 'Uploading' status
            const currentTime = new Date().toLocaleTimeString();
            const fileName = file.name;

            // Create a new row for the upload status
            const newRow = `
                <tr>
                    <td class="border-b border-gray-300 py-1 px-2 text-center">-</td>
                    <td class="border-b border-gray-300 py-1 px-2 text-center">${fileName}</td>
                    <td class="border-b border-gray-300 py-1 px-2 text-center">Uploading...</td>
                </tr>
            `;
            $('#uploadTableBody').prepend(newRow);

            // Upload file to server with AJAX
            var form = new FormData()
            form.append('file', file)

            $.ajax({
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                url: '{{ route("upload.file-upload") }}',
                type: 'POST',
                data: form,
                processData: false,
                contentType: false,
                success: function(response) {
                    console.log('File uploaded successfully:', response);
                    // Update the status to 'Completed' in the table
                    $('#uploadTableBody tr:first').attr('data-upload-id', response.upload_id);
                    $('#uploadTableBody tr:first td:nth-child(1)').text(response.created_at);
                    $('#uploadTableBody tr:first td:nth-child(3)').addClass('text-blue-500').text('Processing');
                },
                error: function(xhr, status, error) {
                    console.error('File upload failed:', error);
                    // Update the status to 'Failed' in the table
                    $('#uploadTableBody tr:first td:nth-child(3)').addClass('text-red-500').text('Failed to Upload');
                },
                complete: function() {
                    // Clear the file input
                    $('#file-ipt').val('');
                }
            });
        });

        // Polling the server for upload status updates
        setInterval(() => {
            $.ajax({
                url: '{{ route("upload.poll") }}',
                type: 'GET',
                success: function(response) {
                    response.uploads.forEach(upload => {
                        const row = $(`#uploadTableBody tr[data-upload-id="${upload.id}"]`);
                        if (row.length) {
                            let statusText = '';
                            if (upload.status === 'completed') {
                                statusText = '<span class="text-green-500">Completed</span>';
                            } else if (upload.status === 'processing') {
                                statusText = '<span class="text-blue-500">Processing</span>';
                            } else if (upload.status === 'failed') {
                                statusText = '<span class="text-red-500">Failed to Process</span>';
                            } else {
                                statusText = '<span>-</span>';
                            }
                            row.find('td:nth-child(3)').html(statusText);
                        }
                    });
                },
                error: function(xhr, status, error) {
                    console.error('Status check failed:', error);
                }
            });
        }, POLLING_INTERVAL);
    </script>
</html>
