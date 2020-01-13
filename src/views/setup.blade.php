@if(config('custom.PKG_DEV'))
    <?php $coa_pkg_prefix = '/packages/abs/coa-pkg/src';?>
@else
    <?php $coa_pkg_prefix = '';?>
@endif

<script type="text/javascript">

    var coa_type_list_template_url = "{{URL::asset($coa_pkg_prefix.'/public/angular/coa-pkg/pages/coa-type/list.html')}}";
    var coa_type_form_template_url = "{{URL::asset($coa_pkg_prefix.'/public/angular/coa-pkg/pages/coa-type/form.html')}}";
    var coa_type_get_form_data_url = "{{url('/coa-pkg/coa-type/get-form-data/')}}";
    var coa_type_delete_url = "{{url('/coa-pkg/coa-type/delete/')}}";

    var coa_posting_type_list_template_url = "{{URL::asset($coa_pkg_prefix.'/public/angular/coa-pkg/pages/coa-posting-type/list.html')}}";
    var coa_posting_type_form_template_url = "{{URL::asset($coa_pkg_prefix.'/public/angular/coa-pkg/pages/coa-posting-type/form.html')}}";
    var coa_posting_type_get_form_data_url = "{{url('/coa-pkg/coa-posting-type/get-form-data/')}}";
    var coa_posting_type_delete_url = "{{url('/coa-pkg/coa-posting-type/delete/')}}";

    var coa_code_list_template_url = "{{URL::asset($coa_pkg_prefix.'/public/angular/coa-pkg/pages/coa-code/list.html')}}";
    var coa_code_form_template_url = "{{URL::asset($coa_pkg_prefix.'/public/angular/coa-pkg/pages/coa-code/form.html')}}";
    var coa_code_get_form_data_url = "{{url('/coa-pkg/coa-code/get-form-data/')}}";
    var coa_code_delete_url = "{{url('/coa-pkg/coa-code/delete/')}}";
    var coa_code_filter_url = "{{route('CoaCodeFilter')}}";

</script>
<script type="text/javascript" src="{{URL::asset($coa_pkg_prefix.'/public/angular/coa-pkg/pages/coa-type/controller.js?v=2')}}"></script>
<script type="text/javascript" src="{{URL::asset($coa_pkg_prefix.'/public/angular/coa-pkg/pages/coa-posting-type/controller.js?v=2')}}"></script>
<script type="text/javascript" src="{{URL::asset($coa_pkg_prefix.'/public/angular/coa-pkg/pages/coa-code/controller.js?v=2')}}"></script>
