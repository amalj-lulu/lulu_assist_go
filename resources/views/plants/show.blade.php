 <div data-modal-title="Plant Details"></div>
 <div class="card-body">
     <table class="table table-bordered">
         <tbody>
             <tr>
                 <th>Name</th>
                 <td>{{ $plant->name }}</td>
             </tr>
             <tr>
                 <th>Code</th>
                 <td>{{ $plant->code }}</td>
             </tr>
             <tr>
                 <th>Assigned Users</th>
                 <td>
                     @if ($plant->users->isEmpty())
                         <p>No users assigned.</p>
                     @else
                         <ul class="mb-0">
                             @foreach ($plant->users as $user)
                                 <li>{{ $user->name }} ({{ $user->email }})</li>
                             @endforeach
                         </ul>
                     @endif
                 </td>
             </tr>
         </tbody>
     </table>
 </div>
