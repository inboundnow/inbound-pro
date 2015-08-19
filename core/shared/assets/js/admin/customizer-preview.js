var Module = (function () {

  var _privateMethod = function () {};

  var myObject = {
    someMethod:  function () {

    },
    anotherMethod:  function () {

    }
  };

  return myObject;

})();

var ModuleTwo = (function (Module) {

    Module.extension = function () {
        // another method!
    };

    return Module;

})(Module || {});