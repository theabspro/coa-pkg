app.config(['$routeProvider', function($routeProvider) {

    $routeProvider.
    //COA CODE
    when('/coa-pkg/coa-code/list', {
        template: '<coa-code-list></coa-code-list>',
        title: 'COA Codes',
    }).
    when('/coa-pkg/coa-code/add', {
        template: '<coa-code-form></coa-code-form>',
        title: 'Add COA Code',
    }).
    when('/coa-pkg/coa-code/edit/:id', {
        template: '<coa-code-form></coa-code-form>',
        title: 'Edit COA Code',
    }).

    //COA TYPE
    when('/coa-pkg/coa-type/list', {
        template: '<coa-type-list></coa-type-list>',
        title: 'COA Types',
    }).
    when('/coa-pkg/coa-type/add', {
        template: '<coa-type-form></coa-type-form>',
        title: 'Add COA Type',
    }).
    when('/coa-pkg/coa-type/edit/:id', {
        template: '<coa-type-form></coa-type-form>',
        title: 'Edit COA Type',
    }).

    //COA POSTING TYPE
    when('/coa-pkg/coa-posting-type/list', {
        template: '<coa-posting-type-list></coa-posting-type-list>',
        title: 'COA Posting Types',
    }).
    when('/coa-pkg/coa-posting-type/add', {
        template: '<coa-posting-type-form></coa-posting-type-form>',
        title: 'Add COA Posting Type',
    }).
    when('/coa-pkg/coa-posting-type/edit/:id', {
        template: '<coa-posting-type-form></coa-posting-type-form>',
        title: 'Edit COA Posting Type',
    });
}]);

app.component('coaCodeList', {
    templateUrl: coa_code_list_template_url,
    controller: function($http, $location, HelperService, $scope, $routeParams, $rootScope, $element) {
        var self = this;
        self.hasPermission = HelperService.hasPermission;
        $http.get(
            coa_code_filter_url
        ).then(function(response) {
            self.extras = response.data.extras;
            self.status_filter = response.data.status_filter;
            $rootScope.loading = false;
        });

        $scope.getCookie = function(name) {
            var value = "; " + document.cookie;
            var parts = value.split("; " + name + "=");
            if (parts.length == 2) return parts.pop().split(";").shift();
        }
        var search_coa_code_cookie = $scope.getCookie('search_coa_code');
        $('#search_coa_code').val(search_coa_code_cookie);

        var table_scroll;
        table_scroll = $('.page-main-content.list-page-content').height() - 37;
        var dataTable = $('#coa-code-table').dataTable({
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
                url: laravel_routes['getCoaCodeList'],
                type: "GET",
                dataType: "json",
                data: function(d) {
                    d.coa_code = $('#coa_code').val();
                    d.description = $('#description').val();
                    d.coa_type = $('#coa_type').val();
                    d.posting_type = $('#posting_type').val();
                    d.currency_code = $('#currency_code').val();
                    d.proposal_type = $('#proposal_type').val();
                    d.status = $('#status').val();
                }
            },

            columns: [
                { data: 'action', searchable: false, class: 'action' },
                { data: 'code', name: 'coa_codes.code', searchable: true },
                { data: 'name', name: 'coa_codes.name', searchable: true },
                { data: 'coa_type_name', name: 'coa_types.name', searchable: true },
                { data: 'coa_posting_type_name', name: 'coa_posting_types.name', searchable: true },
                { data: 'currency_code', name: 'configs.name', searchable: true },
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
        $("#search_coa_code").keyup(function() { //alert(this.value);
            var filter_coa = this.value;
            dataTable.fnFilter(filter_coa);
            document.cookie = "search_coa_code=" + filter_coa;
        });

        $(".search_clear").on("click", function() {
            $('#search_coa_code').val('');
            document.cookie = "search_name=; expires=Thu, 01 Jan 1970 00:00:01 GMT;";
            $('#coa-code-table').DataTable().search('').draw();
        });
        //TABLE-FILTER
        $('#coa_code').keyup(function() {
            dataTable.fnFilter();
        });
        $('#description').keyup(function() {
            dataTable.fnFilter();
        });
        $scope.onSelectedType = function(selected_type_id) {
            setTimeout(function() {
                $('#coa_type').val(selected_type_id);
                dataTable.fnFilter();
            }, 900);
        }
        $scope.onSelectedPostingType = function(selected_posting_type_id) {
            setTimeout(function() {
                $('#posting_type').val(selected_posting_type_id);
                dataTable.fnFilter();
            }, 900);
        }
        $scope.onSelectedCurrencyCode = function(selected_currency_code_id) {
            setTimeout(function() {
                $('#currency_code').val(selected_currency_code_id);
                dataTable.fnFilter();
            }, 900);
        }
        $scope.onSelectedProposalType = function(selected_proposal_type_id) {
            setTimeout(function() {
                $('#proposal_type').val(selected_proposal_type_id);
                dataTable.fnFilter();
            }, 900);
        }
        $scope.onSelectedStatus = function(selected_status_id) {
            setTimeout(function() {
                $('#status').val(selected_status_id);
                dataTable.fnFilter();
            }, 900);
        }
        //END-FILTER
        $scope.calldeleteConfirm = function(id) {
            $('#coa_code_id').val(id);
        }
        $scope.deleteCoaCodeConfirm = function() {
            var id = $('#coa_code_id').val();
            $http.get(
                coa_code_delete_url + '/' + id,
            ).then(function(response) {
                if (response.data.success) {
                    $noty = new Noty({
                        type: 'success',
                        layout: 'topRight',
                        text: 'Coa Code Deleted Successfully',
                    }).show();
                    setTimeout(function() {
                        $noty.close();
                    }, 3000);
                    $('#coa-code-table').DataTable().ajax.reload();
                    $scope.$apply();
                }
            });
        }
    }
});

//COA-CODE FORM

app.component('coaCodeForm', {
    templateUrl: coa_code_form_template_url,
    controller: function($http, $location, HelperService, $scope, $routeParams, $window, $element, $rootScope) {
        get_form_data_url = typeof($routeParams.id) == 'undefined' ? coa_code_get_form_data_url : coa_code_get_form_data_url + '/' + $routeParams.id;
        var self = this;
        self.hasPermission = HelperService.hasPermission;
        self.angular_routes = angular_routes;
        $http.get(
            get_form_data_url
        ).then(function(response) {
            //console.log(response.data);
            self.coa_code = response.data.coa_code;
            self.extras = response.data.extras;
            self.action = response.data.action;
            self.title = response.data.title;

            if (response.data.action == "Add") {
                self.switch_value = 'Active';
            }

            if (response.data.action == "Edit") {
                if (self.coa_code.deleted_at == null) {
                    self.switch_value = 'Active';
                } else {
                    self.switch_value = 'Inactive';
                }
            }
            $rootScope.loading = false;
        });

        // $('#submit').click(function() {
        var form_id = '#form';
        var v = jQuery(form_id).validate({
            ignore: "",
            rules: {
                'code': {
                    required: true,
                    minlength: 3,
                    maxlength: 191,
                },
                'name': {
                    required: true,
                    minlength: 3,
                    maxlength: 191,
                },
                'type_id': {
                    required: true,
                },
                'posting_type_id': {
                    required: true,
                },
            },
            submitHandler: function(form) {
                let formData = new FormData($(form_id)[0]);
                $('#submit').button('loading');
                $.ajax({
                        url: laravel_routes['saveCoaCode'],
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
                            if (res.comes_from != '') {
                                $noty = new Noty({
                                    type: 'success',
                                    layout: 'topRight',
                                    text: 'Coa Code ' + res.comes_from + ' Successfully',
                                }).show();
                            }
                            setTimeout(function() {
                                $noty.close();
                            }, 3000);
                            // custom_noty('success', 'Coa Type ' + res.comes_from + ' Successfully');
                            $('#submit').button('reset');

                            $location.path('/coa-pkg/coa-code/list')
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
        //});
    }
});