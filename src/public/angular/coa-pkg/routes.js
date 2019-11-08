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