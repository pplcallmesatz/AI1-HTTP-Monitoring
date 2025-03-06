<!-- ... existing table headers ... -->
<th>Status</th>
<th>Response Code</th>
<th>Message</th>
<!-- ... -->

<!-- ... in the table rows ... -->
<td>
    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $log->status === 'Active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
        {{ $log->status }}
    </span>
</td>
<td>
    {{ $log->status_code !== 0 ? $log->status_code : 'Connection Failed' }}
</td>
<td class="text-sm">
    {{ $log->message }}
</td>
<!-- ... -->