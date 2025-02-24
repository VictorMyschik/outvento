<div class="bg-white rounded shadow-sm mb-3">
  <div class="table-responsive">
    <table class="table table-striped">
      <thead>
      <tr>
        @foreach($rows['header'] as $head)
              <th class="fw-bold">{{$head}}</th>
          @endforeach
      </tr>
      </thead>
        <tbody>
        @foreach($rows['body'] as $row)
            <tr>
                @foreach($rows['header'] as $head)
                    <td>
                        {!! $row[$head] !!}
                    </td>
                @endforeach
            </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
