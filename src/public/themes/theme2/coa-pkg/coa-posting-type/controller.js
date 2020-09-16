app.component('coaPostingTypeList', {
    templateUrl: coa_posting_type_list_template_url,
    controller: function($http, $location, HelperService, $scope, $routeParams, $rootScope, $element) {
        var self = this;
        self.hasPermission = HelperService.hasPermission;

        $scope.getCookie = function (name) {
          var value = "; " + document.cookie;
          var parts = value.split("; " + name + "=");
          if (parts.length == 2) return parts.pop().split(";").shift();
        }
        var search_cookie = $scope.getCookie('search_name');
        $('#search_coa_posting_type').val(search_cookie);

        var table_scroll;
        table_scroll = $('.page-main-content.list-page-content').height() - 37;
        var dataTable = $('#coa-posting-type-table').dataTable({
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
                url: laravel_routes['getCoaPostingTypeList'],
                type: "GET",
                dataType: "json",
                data: function(d) {
                }
            },

            columns: [
                { data: 'action', searchable: false, class: 'action' },
                { data: 'name', name: 'coa_posting_types.name', searchable: true },
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
        $("#search_coa_posting_type").keyup(function() { //alert(this.value);
            var filter_value = this.value;
            dataTable.fnFilter(filter_value);
            document.cookie = "search_name="+filter_value;
        });
        
        $(".search_clear").on("click", function() {
            $('#search_coa_posting_type').val('');
            document.cookie = "search_name=; expires=Thu, 01 Jan 1970 00:00:01 GMT;";
            $('#coa-posting-type-table').DataTable().search('').draw();
        });

        $scope.calldeleteConfirm = function(id) {
            $('#coa_posting_type_id').val(id);
        }
        $scope.deleteCoaPostingTypeConfirm = function() {
            var id = $('#coa_posting_type_id').val();
            $http.get(
                coa_posting_type_delete_url + '/' + id,
            ).then(function(response) {
                if (response.data.success) {
                    $noty =new Noty({
                        type: 'success',
                        layout: 'topRight',
                        text: 'Coa Posting Type Deleted Successfully',
                    }).show();
                    setTimeout(function() {
                        $noty.close();
                    }, 3000);
                    $('#coa-posting-type-table').DataTable().ajax.reload();
                    $scope.$apply();
                }
            });
        }
    }
});

//COA-TYPE FORM

app.component('coaPostingTypeForm', {
    templateUrl: coa_posting_type_form_template_url,
    controller: function($http, $location, HelperService, $scope, $routeParams, $window, $element, $rootScope) {
        get_form_data_url = typeof($routeParams.id) == 'undefined' ? coa_posting_type_get_form_data_url : coa_posting_type_get_form_data_url + '/' + $routeParams.id;
        var self = this;
        self.hasPermission = HelperService.hasPermission;
        self.angular_routes = angular_routes;
        $http.get(
            get_form_data_url
        ).then(function(response) {
//console.log(response.data);
            self.coa_posting_types = response.data.coa_posting_types;
            self.action = response.data.action;
            self.title = response.data.title;
            self.coa_posting_type_removal_ids = [];
            $rootScope.loading = false;
        });

        self.addNewCoaPostingType = function() {
            self.coa_posting_types.push({
                id: '',
                company_id:'',
                name: '',
                switch_value: 'Active',
            });
        }

        self.removeCoaPostingType = function(index, coa_posting_type_id) {
            if(coa_posting_type_id) {
                self.coa_posting_type_removal_ids.push(coa_posting_type_id);
                $('#coa_posting_type_removal_ids').val(JSON.stringify(self.coa_posting_type_removal_ids));
            }
            self.coa_posting_types.splice(index, 1);
        }

        // $('#submit').click(function() {
            var form_id = '#form';
            $.validator.addClassRules({
                coa_posting_type_name: {
                    minlength: 3,
                    maxlength: 191,
                    required:true,
                },
            });

            var v = jQuery(form_id).validate({
                ignore: "",
                // rules: {
                //     'name': {
                //         required: true,
                //         minlength: 3,
                //         maxlength: 191,
                //     },
                // },
                submitHandler: function(form) {
                    let formData = new FormData($(form_id)[0]);
                    $('#submit').button('loading');
                    $.ajax({
                            url: laravel_routes['saveCoaPostingType'],
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
                                $noty =new Noty({
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
                                if(res.comes_from != '') {
                                    $noty =new Noty({
                                    type: 'success',
                                    layout: 'topRight',
                                    text: 'Coa Posting Type ' + res.comes_from + ' Successfully',
                                }).show();
                                }
                                setTimeout(function() {
                                    $noty.close();
                                }, 3000);
                                // custom_noty('success', 'Coa Type ' + res.comes_from + ' Successfully');
                                $('#submit').button('reset');

                                $location.path('/coa-pkg/coa-posting-type/list')
                                $scope.$apply()
                            }
                        })
                        .fail(function(xhr) {
                            $('#submit').button('reset');
                            $noty =new Noty({
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
        //});
    }
});