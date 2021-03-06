@extends('layouts.admin')

@inject('authority','Authority')
@inject('HRM','HRM')

@section('css')
    <!-- data table -->
    <link rel="stylesheet" href="{{source('plug_in/datatables/datatables.min.css')}}" />
    <!-- zTree css -->
    <link rel="stylesheet" href="{{source('plug_in/ztree/css/metroStyle.css')}}" />
    <!-- checkbox -->
    <link rel="stylesheet" href="{{source('css/checkbox.css')}}" />
@endsection

@section('content')

    <div class="row">
        <div class="col-sm-12">
            <section class="panel">
                <header class="panel-heading">
                    店铺管理
                </header>
                <!-- 筛选 start -->
            @include('hr/shop_filter')
            <!-- 筛选 end -->
                <div class="panel-body">
                    <table class="table table-striped table-bordered dataTable no-footer" id="shop_list">
                    </table>
                </div>
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
                <h4 class="modal-title">新建店铺</h4>
            </div>
            <div class="modal-content">
                <form id="addForm" name="addForm" class="form-horizontal" method="post"
                      action="{{source(route('hr.shop.submit'))}}">
                    @inject('HRM','HRM')
                    @include('hr/shop_form',['type'=>'add'])
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
                <h4 class="modal-title">编辑店铺</h4>
            </div>
            <div class="modal-content">
                <form id="editForm" name="editForm" class="form-horizontal" method="post"
                      action="{{source(route('hr.shop.submit'))}}">
                    @include('hr/shop_form',['type'=>'edit'])
                    <input type="hidden" name="id">
                </form>
            </div>
        </div>
    </div>
    <div id="poiByOne" class="modal fade">
        <div class="modal-dialog">
            <div class="modal-header">
                <button aria-hidden="true" data-dismiss="modal" class="close" type="button">×</button>
                <h4 class="modal-title">店铺定位</h4>
            </div>
            <div class="modal-content">
                <form id="poiForm" name="poiForm" class="form-horizontal" method="post"
                    action="{{source(route('hr.shop.position'))}}">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="control-label col-sm-3">店铺信息</label>
                        <div class="col-sm-8">
                            <input class="form-control" id="shop-name" value="" disabled/>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-3">店铺定位</label>
                        <div class="col-sm-8">
                            <input class="form-control" id="poi-name" placeholder="输入店铺地址搜索"/>
                        </div>
                    </div>
                    <div id="containerMap"></div>
                    <input id="shop-id" type="hidden" name="id">
                    <input id="latitude" type="hidden" name="latitude"/>
                    <input id="longitude" type="hidden" name="longitude"/>
                    <input id="geohash" type="hidden" name="geo_hash"/>
                    <input id="location" type="hidden" name="location"/>
                </div>
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
    <!-- 高德地图 -->
    <script src='//webapi.amap.com/maps?v=1.4.2&key=9a54ee2044c8fdd03b3d953d4ace2b4d'></script>
    <script src="//webapi.amap.com/ui/1.0/main.js?v=1.0.11"></script>
    <!-- zTree js -->
    <script type="text/javascript" src="{{source('plug_in/ztree/js/jquery.ztree.all.js')}}"></script>
    <!--data table-->
    <script type="text/javascript" src="{{source('plug_in/datatables/datatables.min.js')}}"></script>
    <!--script for this view-->
    <script type="text/javascript" src="{{source('js/HR/shop.js')}}"></script>
    <script type="text/javascript" src="{{source('js/HR/geohash.js')}}"></script>
    <script>
      var columns = [
        { data: "shop_sn", title: "店铺代码" },
        { data: "name", title: "店铺名称" },
        { data: "department.name", title: "所属部门" },
        { data: "brand.name", title: "所属品牌" },
        { data: "{province.name}.'-'.{city.name}.'-'.{county.name}.' '.{address}", title: "店铺地址" },
        { data: "clock_in", title: "上班时间", searchable: false },
        { data: "clock_out", title: "下班时间", searchable: false },
        { data: "manager_name", title: "店长" },
        { data: "manager.mobile", title: "店长电话", searchable: false, sortable: false, defaultContent: "" },
        {
          data: "staff.realname", title: "店员", searchable: false, sortable: false, defaultContent: "",
          createdCell: function (nTd, sData, oData, iRow, iCol) {
            var html = '';
            for (var i in oData.staff) {
              var staff = oData.staff[i];
              html += staff.realname + ',';
            }
            $(nTd).html(html);
          }
        },
        {
          data: "id", title: "操作", sortable: false, width: "50px",
          createdCell: function (nTd, sData, oData, iRow, iCol) {
            delete oData.password;
            delete oData.salt;
            var html = '';
              <?php if (check_authority(72)) : ?>
                html += '<button class="btn btn-sm btn-default" title="编辑" onclick=\'edit(' + sData + ')\'><i class="fa fa-edit fa-fw"></i></button> ';
              <?php endif; ?>
              <?php if (check_authority(73)) : ?>
                html += '&nbsp;<button class="btn btn-sm btn-danger" title="删除" onclick="del(' + sData + ')"><i class="fa fa-trash-o fa-fw"></i></button>';
              <?php endif; ?>
              <?php if (check_authority(73)) : ?>
                html += '&nbsp;<button class="btn btn-sm btn-default" title="定位" data-toggle="modal" href="#poiByOne" data-id="'+sData+'" data-name="'+oData.name+'" data-lng="'+oData.lng+'" data-lat="'+oData.lat+'"><i class="fa fa-map-marker fa-fw"></i></button>';
              <?php endif; ?>
              $(nTd).html(html).css({ "padding": "5px", "text-align": "center" });
          }
        }
      ];
      var buttons = [
        { "text": "<i class='fa fa-plus fa-fw'></i>", "action": addShop, "titleAttr": "添加" }
      ];
    </script>


@endsection