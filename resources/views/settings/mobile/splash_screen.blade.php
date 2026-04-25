@extends('layouts.app')
@section('content')
    <div class="page-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-themecolor">Splash Screen Setting</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                    <li class="breadcrumb-item active">Splash Screen Setting</li>
                </ol>
            </div>
        </div>
        <div class="card-body">
            <div id="data-table_processing" class="dataTables_processing panel panel-default" style="display: none;">
                {{trans('lang.processing')}}
            </div>
            <div class="row restaurant_payout_create">
                <div class="restaurant_payout_create-inner">
                    <fieldset>
                        <legend><i class="mr-3 fa fa-mobile"></i>Splash Screen Settings</legend>
                        
                        <div class="form-group row">
                            <label class="col-3 control-label">Select App</label>
                            <div class="col-7">
                                <select class="form-control" id="app_select">
                                    <option value="customer">Customer App</option>
                                    <option value="driver">Driver App</option>
                                    <option value="seller">Seller App</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-3 control-label">Background Color</label>
                            <div class="col-7">
                                <input type="color" class="form-control" id="splash_color">
                                <div class="form-text text-muted">Select the background color for the splash screen.</div>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-3 control-label">Logo / Splash Image</label>
                            <div class="col-7">
                                <input type="file" onChange="handleFileSelect(event)" class="form-control">
                                <div id="uploding_image"></div>
                                <div class="placeholder_img_thumb">
                                    <img id="app_splash_logo" src="" width="100px" alt="">
                                </div>
                                <div class="form-text text-muted">
                                    Recommended size: 1080 x 1920 pixels (Aspect Ratio 9:16).
                                </div>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-3 control-label">Tagline 1</label>
                            <div class="col-7">
                                <input type="text" class="form-control" id="title1">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-3 control-label">Tagline 2</label>
                            <div class="col-7">
                                <input type="text" class="form-control" id="title2">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-3 control-label">Tagline 3</label>
                            <div class="col-7">
                                <input type="text" class="form-control" id="title3">
                            </div>
                        </div>

                    </fieldset>
                </div>
            </div>
        </div>
        <div class="form-group col-12 text-center btm-btn">
            <button type="button" class="btn btn-primary save_splash_btn"><i class="fa fa-save"></i> {{trans('lang.save')}}</button>
            <a href="{{url('/dashboard')}}" class="btn btn-default"><i class="fa fa-undo"></i>{{trans('lang.cancel')}}</a>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/3.1.9-1/crypto-js.js"></script>
    <script>
        var database = firebase.firestore();
        var storageRef = firebase.storage().ref('images');
        var photo = "";

        $(document).ready(function () {
            loadSplashConfig('customer');
        });

        $('#app_select').change(function() {
            loadSplashConfig($(this).val());
        });

        function loadSplashConfig(appType) {
            $("#data-table_processing").show();
            database.collection('splash_config').doc(appType).get().then(function (snapshot) {
                if (snapshot.exists) {
                    var data = snapshot.data();
                    $("#splash_color").val(data.color || "#FC5B00");
                    $("#title1").val(data.title1 || "");
                    $("#title2").val(data.title2 || "");
                    $("#title3").val(data.title3 || "");
                    photo = data.image || "";
                    if (photo != "") {
                        $("#app_splash_logo").attr('src', photo);
                    } else {
                        $("#app_splash_logo").attr('src', "");
                    }
                } else {
                    // Reset to defaults
                    $("#splash_color").val("#FC5B00");
                    $("#title1").val("");
                    $("#title2").val("");
                    $("#title3").val("");
                    $("#app_splash_logo").attr('src', "");
                    photo = "";
                }
                $("#data-table_processing").hide();
            });
        }

        $(".save_splash_btn").click(function () {
            var appType = $("#app_select").val();
            var color = $("#splash_color").val();
            var title1 = $("#title1").val();
            var title2 = $("#title2").val();
            var title3 = $("#title3").val();

            $("#data-table_processing").show();
            database.collection('splash_config').doc(appType).set({
                'image': photo,
                'color': color,
                'title1': title1,
                'title2': title2,
                'title3': title3
            }).then(function (result) {
                $("#data-table_processing").hide();
                alert('Splash Screen configuration saved for ' + appType);
            }).catch(function(error) {
                $("#data-table_processing").hide();
                console.error("Error saving splash config: ", error);
            });
        });

        function handleFileSelect(evt) {
            var f = evt.target.files[0];
            var reader = new FileReader();
            reader.onload = (function (theFile) {
                return function (e) {
                    var filePayload = e.target.result;
                    var hash = CryptoJS.SHA256(Math.random() + CryptoJS.SHA256(filePayload));
                    var val = f.name;
                    var ext = val.split('.')[1];
                    var filename = (f.name).replace(/C:\\fakepath\\/i, '')
                    var timestamp = Number(new Date());
                    var uploadTask = storageRef.child(filename).put(theFile);
                    uploadTask.on('state_changed', function (snapshot) {
                        var progress = (snapshot.bytesTransferred / snapshot.totalBytes) * 100;
                        jQuery("#uploding_image").text("Image is uploading...");
                    }, function (error) {
                        console.error(error);
                    }, function () {
                        uploadTask.snapshot.ref.getDownloadURL().then(function (downloadURL) {
                            jQuery("#uploding_image").text("Upload is completed");
                            photo = downloadURL;
                            $("#app_splash_logo").attr('src', photo);
                        });
                    });
                };
            })(f);
            reader.readAsDataURL(f);
        }
    </script>
@endsection
