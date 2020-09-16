app.component('coaTypeList', {
    templateUrl: coa_type_list_template_url,
    controller: function($http, $location, HelperService, $scope, $routeParams, $rootScope, $element) {
        var self = this;
        self.hasPermission = HelperService.hasPermission;

        $scope.getCookie = function (name) {
          var value = "; " + document.cookie;
          var parts = value.split("; " + name + "=");
          if (parts.length == 2) return parts.pop().split(";").shift();
        }
        var search_name_cookie = $scope.getCookie('filter_name');
        $('#search_coa_type').val(search_name_cookie);

        var table_scroll;
        table_scroll = $('.page-main-content.list-page-content').height() - 37;
        var dataTable = $('#coa-type-table').dataTable({
            "dom": cndn_dom_structure,
            "language": {
                // "search": "",
                // "searchPlaceholder": "Search",
                "lengthMenu": "Rows _MENU_",
                "paginate": {
                    "next": '<i class="icon ion-ios-arrow-forward"></i>',
                    "previous": '<i class="icon ion-ios-arrow-back"></i>'
                },
            },
            stateSave: true,
            processing: true,
            serverSide: true,
            paging: true,
            searching: true,
            ordering: false,
            scrollY: table_scroll + "px",
            scrollCollapse: true,
            ajax: {
                url: laravel_routes['getCoaTypeList'],
                type: "GET",
                dataType: "json",
                data: function(d) {
                }
            },

            columns: [
                { data: 'action', searchable: false, class: 'action' },
                { data: 'name', name: 'coa_types.name', searchable: true },
                { data: 'coa_code_names', name: 'coa_codes.id', searchable: false },
                // { data: 'status', searchable: false },
            ],
            rowCallback: function(row, data) {
                $(row).addClass('highlight-row');
            },
            infoCallback: function(settings, start, end, max, total, pre) {
                $('#table_info').html(total)
                $('.foot_info').html('Showing ' + start + ' to ' + end + ' of ' + max + ' Listing')
            },
        });
        $('.dataTables_length select').select2();
        $("#search_coa_type").keyup(function() { //alert(this.value);
            var search_value = this.value;
            dataTable.fnFilter(search_value);
            document.cookie = "filter_name="+search_value;
        });
        
        $(".search_clear").on("click", function() {
            $('#search_coa_type').val('');
            document.cookie = "filter_name=; expires=Thu, 01 Jan 1970 00:00:01 GMT;";
            $('#coa-type-table').DataTable().search('').draw();
        });

        $scope.calldeleteConfirm = function(id) {
            $('#coa_type_id').val(id);
        }
        $scope.deleteCoaTypeConfirm = function() {
            var id = $('#coa_type_id').val();
            $http.get(
                coa_type_delete_url + '/' + id,
            ).then(function(response) {
                if (response.data.success) {
                    $noty = new Noty({
                        type: 'success',
                        layout: 'topRight',
                        text: 'Coa Type Deleted Successfully',
                    }).show();
                    setTimeout(function() {
                        $noty.close();
                    }, 3000);
                    $('#coa-type-table').DataTable().ajax.reload();
                    $scope.$apply();
                }
            });
        }
    }
});

//COA-TYPE FORM

app.component('coaTypeForm', {
    templateUrl: coa_type_form_template_url,
    controller: function($http, $location, HelperService, $scope, $routeParams, $window, $element, $rootScope) {
        get_form_data_url = typeof($routeParams.id) == 'undefined' ? coa_type_get_form_data_url : coa_type_get_form_data_url + '/' + $routeParams.id;
        var self = this;
        self.hasPermission = HelperService.hasPermission;
        self.angular_routes = angular_routes;
        $http.get(
            get_form_data_url
        ).then(function(response) {
// console.log(response.data);
            self.coa_type = response.data.coa_type;
            self.extras = response.data.extras;
            self.action = response.data.action;
            self.title = response.data.title;
            self.coa_code_removal_ids = [];
            
            if (response.data.action == "Add") {
                self.switch_value = 'Active';
            }

            if (response.data.action == "Edit") {
                if (self.coa_type.deleted_at == null) {
                    self.switch_value = 'Active';
                } else {
                    self.switch_value = 'Inactive';
                }
            }
            $rootScope.loading = false;
        });

        /* Tab Funtion */
        $('.btn-nxt').on("click", function() {
            $('.cndn-tabs li.active').next().children('a').trigger("click");
            tabPaneFooter();
        });
        $('.btn-prev').on("click", function() {
            $('.cndn-tabs li.active').prev().children('a').trigger("click");
            tabPaneFooter();
        });

        self.addNewCoaCode = function() {
            self.coa_type.coa_codes.push({
                id: '',
                company_id:'',
                code:'',
                name: '',
            });
        }

        self.removeCoaCode = function(index, coa_code_id) {
            if(coa_code_id) {
                self.coa_code_removal_ids.push(coa_code_id);
                $('#coa_code_removal_ids').val(JSON.stringify(self.coa_code_removal_ids));
            }
            self.coa_type.coa_codes.splice(index, 1);
        }
        $('#submit').click(function() { 
            var form_id = '#form';
            $.validator.addClassRules({
                coa_code_valid: {
                    minlength: 3,
                    maxlength: 191,
                    required:true,
                },
                coa_code_name: {
                    minlength: 3,
                    maxlength: 191,
                    required:true,
                },
            });

            var v = jQuery(form_id).validate({
                ignore: "",
                rules: {
                    'name': {
                        required: true,
                        minlength: 3,
                        maxlength: 191,
                    },
                },
                messages: {
                    'name': {
                        minlength: 'Enter atleast 3 characters',
                    },
                },
                invalidHandler: function(event, validator) {
                    // custom_noty('error', 'Please check in each tab and fix errors!');
                    $noty = new Noty({
                        type: 'error',
                        layout: 'topRight',
                        text: 'Please check in each tab and fix errors!',
                    }).show();
                    setTimeout(function() {
                        $noty.close();
                    }, 3000);
                },
                submitHandler: function(form) {
                    let formData = new FormData($(form_id)[0]);
                    $('#submit').button('loading');
                    $.ajax({
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            url: laravel_routes['saveCoaType'],
                            method: "POST",
                            data: formData,
                            processData: false,
                            contentType: false,
                        })
                        .done(function(res) {
                            // console.log(res.success);
                            if (!res.success) {
                                // $('#submit').button('reset');
                                $('#submit').prop('disabled', 'disabled');
                                var errors = '';
                                for (var i in res.errors) {
                                    errors += '<li>' + res.errors[i] + '</li>';
                                }
                                $noty = new Noty({
                                    type: 'error',
                                    layout: 'topRight',
                                    text: errors
                                }).show();
                                setTimeout(function() {
                                    $noty.close();
                                }, 3000);
                                // custom_noty('error', errors);
                                $('#submit').button('reset');

                            } else {
                                $noty = new Noty({
                                    type: 'success',
                                    layout: 'topRight',
                                    text: 'Coa Type ' + res.comes_from + ' Successfully',
                                }).show();
                                setTimeout(function() {
                                    $noty.close();
                                }, 3000);
                                // custom_noty('success', 'Coa Type ' + res.comes_from + ' Successfully');
                                $('#submit').button('reset');

                                $location.path('/coa-pkg/coa-type/list')
                                $scope.$apply()
                            }
                        })
                        .fail(function(xhr) {
                            $('#submit').button('reset');
                            $noty = new Noty({
                                type: 'error',
                                layout: 'topRight',
                                text: 'Something went wrong at server',
                            }).show();
                            setTimeout(function() {
                                $noty.close();
                            }, 3000);
                            // custom_noty('error', 'Something went wrong at server');
                        });
                },
            });
        });
    }
});