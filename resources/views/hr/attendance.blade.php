@extends('layouts.admin')

@inject('authority','Authority')
@inject('HRM','HRM')

@section('css')
<!-- data table   -->
<link rel="stylesheet" href="{{asset('plug_in/datatables/css/dataTables.bootstrap.css')}}" />
<link rel="stylesheet" href="{{asset('plug_in/datatables/css/buttons.dataTables.css')}}" />
<link rel="stylesheet" href="{{asset('plug_in/datatables/css/buttons.bootstrap.css')}}" />
<!-- zTree css -->
<link rel="stylesheet" href="{{asset('plug_in/ztree/css/metroStyle.css')}}" />
<!-- validity -->
<link rel="stylesheet" href="{{asset('plug_in/validity/jquery.validity.css')}}" />
<!-- checkbox -->
<link rel="stylesheet" href="{{asset('css/checkbox.css')}}" />
<!-- datetimepicker  -->
<link rel="stylesheet" href="{{asset('plug_in/datetimepicker/bootstrap-datetimepicker.css')}}" />
@endsection



@section('content')
<div class="row">
	<div class="col-sm-8">
		<section class="panel">
			<header class="panel-heading">
			考勤列表
			</header> 
			<!-- 筛选 start  -->  
			 {{-- @include('hr/staff_filter') --}}
			<!-- 筛选 end -->
			<!-- 列表 start -->
			<div class="panel-body">
				<table class="table table-striped table-bordered dataTable no-footer" id="transfer">
				</table>
			</div>
			<!-- 列表  end -->
		</section>
	</div>
	<section id="board-right"></section>
</div>
 
<!-- AddByOne -->
<button id="openAddByOne" data-toggle="modal" href="#addByOne" class="hidden"></button>
<div id="addByOne" class="modal fade">
	<div class="modal-dialog">
		<div class="modal-header">
			<button aria-hidden="true" data-dismiss="modal" class="close" type="button">×</button>
			<h4 class="modal-title">新建调动</h4>
		</div> 
		<div class="modal-content">
			<form id="addDepartmentForm" name="addDepartmentForm" class="form-horizontal" method="post" action="{{config('api.url.transfer.save')}}">
				@inject('HRM','HRM')
				@include('hr/transfer_form')
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
					<button type="submit" class="btn btn-success">确认</button>
				</div>
			</form>
		</div>
	</div>
</div>

<!-- EditByOne -->
<button id="openEditByOne" data-toggle="modal" href="#editByOne" class="hidden"></button>
<div id="editByOne" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-header">
            <button aria-hidden="true" data-dismiss="modal" class="close" type="button">×</button>
            <h4 class="modal-title">编辑调动信息</h4>
        </div>
        <div class="modal-content">
            <form id="editDepartmentForm" name="editDepartmentForm" class="form-horizontal" method="post" action="{{config('api.url.transfer.edit')}}">
                @include('hr/transfer_form')
                <input type="hidden" name="id" >
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-success">确认</button>
                </div>
            </form>
        </div>
    </div>
</div>


@endsection


@section('js')
<script type="text/javascript" src="{{asset('plug_in/datatables/js/jquery.dataTables.js')}}"></script>
<script type="text/javascript" src="{{asset('plug_in/datatables/js/dataTables.bootstrap.js')}}"></script>
<script type="text/javascript" src="{{asset('plug_in/datatables/js/dataTables.buttons.js')}}"></script>
<script type="text/javascript" src="{{asset('plug_in/datatables/js/buttons.bootstrap.js')}}"></script>
<script type="text/javascript" src="{{asset('plug_in/datatables/js/buttons.colVis.js')}}"></script>
<!-- zTree js -->
<script type="text/javascript" src="{{asset('plug_in/ztree/js/jquery.ztree.all.js')}}"></script>
<!-- validity -->
<script type="text/javascript" src="{{asset('plug_in/validity/jquery.validity.js')}}"></script>
<!-- datetimepicker -->
<script type="text/javascript" src="{{asset('plug_in/datetimepicker/bootstrap-datetimepicker.js')}}"></script>
<!--script for this view-->
<script type="text/javascript" src="{{asset('js/HR/attendance.js')}}"></script>

<script>

var ATTENDANCE = {
    getlist:"{{config('api.url.attendance.getlist')}}",
    cancel:"{{config('api.url.attendance.cancel')}}",
    get_staff_list:"{{config('api.url.attendance.stafflist')}}"
};

var columns = [
	{"data": "id", "title": "编号"},
	{"data": "shop_sn", "title": "店铺代码"},
	{"data": "shop_name", "title": "店铺名称"},
	{"data": "achievement", "title": "总业绩"},
	{"data": "submit_time", "title": "提交时间", "width": "30px"},
	{"data": "status", "title": "状态","render": function (data) {
                var statusName =[
                    '<span class="text-danger">异常</span>',
                    '<span class="text-success">正常</span>',
                ];
                return statusName[data];
            }
     },
	{"data": "id", "title": "操作","sortable": false, "width": "50px","createdCell": function (nTd, sData, oData, iRow, iCol) {
	    delete oData.password;
	    delete oData.salt;
	    var html = '';
	  
	     <?php if ($authority->checkAuthority(40)): ?>
	   
	    html = '<button class="btn btn-sm btn-info" title="店员信息" onclick="showPersonalInfo(' + sData + ')"><i class="fa fa-address-card fa-fw"></i></button> ';
	    <?php endif; ?>
		
	 //    <?php if ($authority->checkAuthority(41)): ?>
	    	 
		//     html += '&nbsp;<button class="btn btn-sm btn-danger" title="取消行程" onclick="del(' + sData + ')"><i class="fa fa-trash-o fa-fw"></i></button>';
		// <?php endif; ?>    
		
		
	    $(nTd).html(html).css({"padding": "5px", "text-align": "center"});

	    }
	},
 
];

var buttons = [
    {"extend": "colvis", "text": "<i class='fa fa-eye-slash fa-fw'></i>", "titleAttr": "可见字段"},
    {"text": "<i class='fa fa-refresh fa-fw'></i>", "titleAttr": "刷新", "action": function () {
            table.fnDraw();
        }
    }
];

</script>
@endsection