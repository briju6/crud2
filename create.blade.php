@extends($adminTheme)

@section('title')
  Aboutus Library
@endsection

@section('content')
<section class="content-header">
  <h1>
    Create Aboutus Library
  </h1>
  <ol class="breadcrumb">
    <li><a href="{{ route('admin.dashboard') }}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
    <li><a href="{{ route('admin.aboutus.index') }}">Aboutus Library</a></li>
    <li class="active">Create Aboutus Library</li>
  </ol>
</section>
<section class="content">
    <div class="row">
        <div class="col-md-12">
          <div class="box box-info">
            <div class="box-header with-border">
              <h3 class="box-title">Create Aboutus Library</h3>
              <div class="pull-right">
                  <a href="{{ route('admin.aboutus.index') }}" data-toggle="tooltip" data-placement="top" data-original-title="Back" class="btn btn-primary btn-sm btn-flat"><i class="fa fa-arrow-left"></i></a>
              </div>
            </div>
          	<div class="box-body">
	          	@if (count($errors) > 0)
	            <div class="row">
	                <div class="col-md-12">
	                    <div class="alert alert-danger alert-dismissible">
	                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
	                        <h4><i class="icon fa fa-ban"></i> Error!</h4>
	                        @foreach($errors->all() as $error)
	                        {{ $error }} <br>
	                        @endforeach      
	                    </div>
	                </div>
	            </div>
	            @endif

	            {!! Form::open(array('route' => 'admin.aboutus.store','method'=>'POST','files'=>'true')) !!}
                  
                  @include('admin.aboutus.form')
              {!! Form::close() !!}
          	</div>
          </div>
        </div>
    </div>
</section>
@endsection
@section('script')
<script src="{{ asset('adminTheme/bower_components/ckeditor/ckeditor.js') }}"></script>
<script type="text/javascript">
    // load CKEDITOR
    CKEDITOR.replace('editor1',{
      filebrowserUploadUrl: ck_file_upload_route,
      filebrowserUploadMethod: 'form'
    });
</script>
@endsection