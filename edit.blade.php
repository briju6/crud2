@extends($adminTheme)

@section('title')
  Banner
@endsection

@section('style')
<link rel="stylesheet" type="text/css" href="{{ asset('/frontTheme/css/page/cms.css') }}">
<link href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/4.0.1/min/dropzone.min.css" rel="stylesheet">
<style type="text/css">
  .dropzone-div{
    margin-bottom: 15px;
  }
  .dropzone .dz-preview .dz-image img{
    height: 100%;
    width: 100%;
  }
  .dz-preview .dz-remove{
    position: relative;
    top: -143px;
  }
  .dz-preview .dz-filename{
    position: relative;
    top: 63px;
  }
  .select2-container{
    width: 500px !important;
  }
</style>
@endsection

@section('content')
<section class="content-header">
  <h1>
    Manage Banner
  </h1>
  <ol class="breadcrumb">
    <li><a href="{{ route('admin.dashboard') }}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
    <li><a href="{{ route('admin.banner.home') }}">Banner</a></li>
    <li class="active">Edit Banner</li>
  </ol>
</section>
<section class="content">
    <div class="row">
        <div class="col-md-12">
          <div class="box box-info">
            <div class="box-header with-border">
              <h3 class="box-title">Edit Banner</h3>
              <div class="pull-right">
                  <a href="{{ route('admin.banner.home') }}" data-toggle="tooltip" data-placement="top" data-original-title="Back" class="btn btn-primary btn-sm btn-flat"><i class="fa fa-arrow-left"></i></a>
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

              {!! Form::model($banner, ['method' => 'PATCH','route' => ["admin.banner.update", $banner->id],'files'=>true]) !!} 
                    @include('admin.banner.form')
              {!! Form::close() !!}

              <!--<div class="dropzone-div text-center">
                {!! Form::open([ 'route' => [ 'admin.banner.file.store' ], 'files' => true, 'enctype' => 'multipart/form-data', 'class' => 'dropzone', 'id' => 'image-upload' ]) !!}
                  <div>
                      <h4>Upload Multiple File By Click On Box</h4>
                  </div>
                {!! Form::close() !!}
              </div>-->
            </div>
          </div>
        </div>
    </div>
</section>
@endsection
@section('script')
<script src="{{ asset('adminTheme/bower_components/ckeditor/ckeditor.js') }}"></script>
<script src="{{ asset('adminTheme/js/page/cms.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/4.2.0/min/dropzone.min.js"></script>
<script type="text/javascript">
  //Date picker
  $('#datepicker-start-date').datepicker({
    autoclose: true,
    format : "dd/mm/yyyy"
  });
  $('#datepicker-video-date').datepicker({
    autoclose: true,
    format : "dd/mm/yyyy"
  });
  $('.datepicker-date').datepicker({
    autoclose: true,
    format : "dd/mm/yyyy"
  });

  $( document ).ready(function() {
   var data = <?php echo $banner->menu_id ?>;
    if (data == 24) {
      $('.news-paper').removeClass('display-none');
      $('.news-paper').addClass('display-block');
    }
   
  });
  

  // start start end date show
  $('.menu-change').on('change', function(){
    var val = $(this).val();

    if (val == 104) {
      $('.news-paper').addClass('display-none');
      $('.news-paper').removeClass('display-block');
      $('.start-end-date-div-main').removeClass('display-none');
      $('.start-end-date-div-main').addClass('display-block');
    }else if(val == 24){
      $('.news-paper').removeClass('display-none');
      $('.news-paper').addClass('display-block');
      $('.start-end-date-div-main').removeClass('display-block');
      $('.start-end-date-div-main').addClass('display-none');
      $('.created-at-div-main').removeClass('display-none');
      $('.created-at-div-main').addClass('display-block');
    }else{
      $('.news-paper').addClass('display-none');
      $('.news-paper').removeClass('display-block');
      $('.start-end-date-div-main').removeClass('display-block');
      $('.start-end-date-div-main').addClass('display-none');
    }
  });
  // end start end date show

  // Dropzone.autoDiscover = false;
  var uurl = <?php echo json_encode($documentFileArray) ?>;

  Dropzone.options.imageUpload = {
      acceptedFiles : ".pdf,.rar,.zip",
      uploadMultiple: false,
      error: function(file, message) {
      $('.dropzone-div').find(".dz-preview").addClass('remove-class');
        alert("File Already Exist");
        $('.dropzone-div .remove-class:last').fadeOut();
      },
      init: function() { 
        myDropzone = this;

        $.each(uurl, function(key,value) {
          var mockFile = { name: value.name, size: value.size};
          var preExtension = value.name.substr(value.name.length -3);
          myDropzone.emit("addedfile", mockFile);
          if (preExtension == "zip" || preExtension == "rar") {
              myDropzone.emit("thumbnail", mockFile, '/adminTheme/image/textfile.png');
          }else{
              myDropzone.emit("thumbnail", mockFile, '/adminTheme/image/pdf-new.png');
          }
            myDropzone.emit("complete", mockFile);
          });
      },
      success: function(file, response){
        var aa = file.previewElement.querySelector("[data-dz-name]");
        aa.innerHTML = response.filepath;
        var extension = response.success.name.substr(response.success.name.length -3);
        if (extension == "rar" || extension == "zip") {
        $(file.previewElement).find(".dz-image img").attr("src", '/adminTheme/image/textfile.png');
          
        }else{
          $(file.previewElement).find(".dz-image img").attr("src", '/adminTheme/image/pdf-new.png');
        }
        $('.file-hidden-field-div').append('<input type="hidden" name="fileArray['+response.success.id+']" value="'+response.success.id+'">');
      },
      addRemoveLinks: true,
      removedfile: function(file) {
        var aa = file.previewElement.querySelector("[data-dz-name]");
        x = confirm('Do you want to delete?');
        if(x){
          var filePath = aa.innerHTML;

          $.ajax({
            url: '/admin/banner/file/destroy',
            method: 'GET',
            data: {filePath:filePath},
            success: function(data) {
              $('.file-hidden-field-div').find('.upload-file-'+data.id+'').remove();
              toastr.success(data.success, 'Success Alert', {timeOut: 5000})
            }
          });

          var _ref;
          return (_ref = file.previewElement) != null ? _ref.parentNode.removeChild(file.previewElement) : void 0;
        }
      }
  };
</script>
@endsection