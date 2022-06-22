@extends($adminTheme)

@section('title')
  Banner
@endsection

@section('style')
<style type="text/css">
  table tr td:first-child{
    width: 150px;
  }
  .gallery-image-box{
    border:2px solid #f1f1f1;
    margin-right: 8px;
    margin-bottom: 8px;
    border-radius: 2px;
    padding: 2px;
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
    <li class="active">Show Banner</li>
  </ol>
</section>
<section class="content">
    <div class="row">
        <div class="col-md-12">
          <div class="box box-info">
            <div class="box-header with-border">
              <h3 class="box-title">Show Banner</h3>
              <div class="pull-right">
                  <a href="{{ route('admin.banner.home') }}" data-toggle="tooltip" data-placement="top" data-original-title="Back" class="btn btn-primary btn-sm btn-flat"><i class="fa fa-arrow-left"></i></a>
              </div>
            </div>
            <div class="box-body">
              <table class="table table-bordered">
                <tbody>
                 
              
			      <tr>
                    <td><strong>Title</strong></td>
                    <td>{{ $banner->title }}</td>
                  </tr>
				   <tr>
                    <td><strong>Banner Image</strong></td>
                    <td>
                      <div class="row">
                          <div class="col-md-12">
                            
                            @if(!empty($banner->page_img))
                                <img src="{{ asset('/public/banner/'.$banner->page_img) }}" height="100" width="300" class="gallery-image-box">
                            @endif
                          </div>
                        </div>
                    </td>
                  </tr>
                 
               
				  
                </tbody>
              </table>
            </div>
          </div>
        </div>
    </div>
</section>
@endsection