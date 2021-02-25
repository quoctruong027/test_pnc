<div class="etcpf-upload-block">
    <div class="etcpf-step" style="text-align:center;">
        <ol>
            <li class="active current">
                Upload
                <span class="step active">1</span>
            </li>
            <li>
                Uploading
                <span class="step">2</span>
            </li>
            <li>
                Finish
                <span class="step">3</span>
            </li>
        </ol>
    </div>
    <form id="regForm" action="#">
        <!--<span class="step"></span>
        <span class="step"></span>-->
        <header>
            <h1>Etsy Feed Upload</h1>
        </header>
        <section class="etcpf-upload-body">
            <!-- One "tab" for each step in the form: -->
            <div class="etcpf-feed-details">
                <div class="tab">Feed Name: hoodies-iii </div>
                <div class="tab">Feed Path: http://local.exportfeed.com/wp-content/uploads/etsy_merchant_feeds/Etsy/hoodies-iii.xml </div>
                <div class="tab">Total product in feed with
                    variation: 127</div>
                <br>
                <div class="tab" style="color:#31708f;display: none;"> Note: Please Click show more to see the upload
                    process in
                    detail.
                </div>
            </div>


        </section>
        <div class="etcpf-footer-action">
            <!--<a href="javascript:void(0);" class="toggle-more-options" data-hidetext="Hide advanced options"
               data-showtext="Hide advanced options">Products that will be uploaded</a>-->
            <span>Products that will be uploaded</span>
            <!--<button type="button" id="prevBtn" onclick="nextPrev(-1)">Previous</button>-->
            <button class="button-primary" type="button" id="etcpfUploadBtn" onclick="return runJsCronForUpload( 3 );">Start Uploading
            </button>
            <div class="spinner"></div>
        </div>
        <div class="etcpf-products-table">
            <table class="striped widefat" id="uploaded-table">
                <thead>
                <tr>
                    <th>Products</th>
                    <th>Listing ID</th>
                    <th>Upload Result</th>
                    <th>Variation Result</th>
                    <th>Status</th>
                </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
        <!-- Circles which indicates the steps of the form: -->
        <input type="hidden" id="etsy-feed-resubmit" name="resubmit-feed" value="0">

        <input type="hidden" id="etsy-feed-uploadfailed" name="uploadfailed-feed" value="0">
    </form>
</div>