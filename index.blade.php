@extends($adminTheme)

@section('title')
  Banner
@endsection

@section('content')
<section class="content-header">
  <h1>
    Manage Banner
  </h1>
  <ol class="breadcrumb">
    <li><a href="{{ route('admin.dashboard') }}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
    <li class="active">Banner</li>
  </ol>
</section>
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box">
                <div class="box-header">
                    <h3 class="box-title">Banner List</h3>
                    <div class="pull-right">
                        <a href="{{ route('admin.banner.create') }}" data-toggle="tooltip" data-placement="top" data-original-title="Create Banner" class="btn btn-success btn-sm btn-flat"><i class="fa fa-plus"></i></a>
                    </div>
                </div>
                <div class="box-body">
                    <table class="table table-striped table-bordered data-table">
                        <thead>
                            <tr>
                                <th class="data-table-no-column" width="20">#</th>
                                <th>Title</th>
                                <th width="70">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                        
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
@section('script')
<script>
    $(function () {
        var table = $('.data-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.banner.home') }}",
            columns: [
                {data: 'DT_RowIndex', name: 'DT_RowIndex'},
                {data: 'title', name: 'title'},            
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });

        $.fn.dataTable.ext.errMode = 'throw';
    });
</script>
@endsection
