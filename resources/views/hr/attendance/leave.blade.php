@extends('layouts.admin')

@inject('authority','Authority')
@inject('HRM','HRM')

@section('css')
<!-- data table -->
<link rel="stylesheet" href="{{source('plug_in/datatables/datatables.min.css')}}" />
<!-- checkbox -->
<link rel="stylesheet" href="{{source('css/checkbox.css')}}" />
@endsection

@section('content')
<div class="row">
    <div class="col-sm-12">
        <section class="panel">
            <header class="panel-heading">
                请假列表
            </header> 
            <!-- 筛选 start --> 
            {{-- @include('hr/staff_filter') --}}
            <!-- 筛选 end -->
            <!-- 列表 start -->
            <div class="panel-body">
                <table class="table table-striped table-bordered dataTable no-footer" id="leave_table">
                </table>
            </div>
            <!-- 列表  end -->
        </section>
    </div>
    <section id="board-right"></section>
</div>

<!-- Import -->
<button id="openAddByOne" data-toggle="modal" href="#addByOne" class="hidden"></button>
<div id="addByOne" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-header">
            <button aria-hidden="true" data-dismiss="modal" class="close" type="button">×</button>
            <h4 class="modal-title">导入数据</h4>
        </div>
        <div class="modal-content">
            <form id="addForm" name="addForm" class="form-horizontal" method="post" enctype ="multipart/form-data" action="/hr/leave/excelhandel">
                @inject('HRM','HRM')
                @include('hr/attendance/leave_from')
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-success" id="daoruid">确认</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- EditByOne -->
<div id="editByOne" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-header">
            <button aria-hidden="true" data-dismiss="modal" class="close" type="button">×</button>
            <h4 class="modal-title">编辑</h4>
        </div>
        <div class="modal-content">
            <form id="editForm" name="editForm" class="form-horizontal" method="post" action="{{config('api.url.transfer.edit')}}">
                @include('hr/attendance/leave_from')
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
<!--data table-->
<script type="text/javascript" src="{{source('plug_in/datatables/datatables.min.js')}}"></script>
<!--script for this view-->
<script type="text/javascript" src="{{source('js/HR/leave.js')}}"></script>

<script>

var HOLIDAY = {
    list: "{{config('api.url.holiday.list')}}",
    cancel: "{{config('api.url.holiday.cancel')}}",
};

var columns = [
    {"data": "id", "title": "编号"},
    {"data": "sponsor", "title": "发起人工号"},
    {"data": "sponsor_name", "title": "发起人名"},
    {"data": "department", "title": "部门"},
    {"data": "start_time", "title": "开始时间"},
    {"data": "end_time", "title": "结束时间"},
    {data: "{subject_status}==1?'完成':'撤销'", name: "subject_status", title: "审批状态"},
    {data: "{subject_status}==1?'同意':'拒绝'", name: "subject_result", title: "审批结果"},
    {data: "id", title: "操作", "createdCell": function (nTd, sData, oData, iRow, iCol) {
            var html = '';
            html += '<button class="btn btn-sm btn-default" title="编辑" onclick="edit(' + sData + ')"><i class="fa fa-edit fa-fw"></i></button>';
            if (oData.subject_status !== 2) {
                html += '&nbsp;<button class="btn btn-sm btn-danger" title="撤销" onclick="del(' + sData + ')"><i class="fa fa-trash-o fa-fw"></i></button>';
            }
            $(nTd).html(html).css({"padding": "5px", "text-align": "center"});
        }
    }
];

var buttons = [
    {"text": "<i class='fa fa-upload fa-fw'></i>", "action": function () {
            imports();
        }, "titleAttr": "导入数据"}
];

</script>
@endsection