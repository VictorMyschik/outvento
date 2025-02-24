<div class="bg-white rounded shadow-sm mb-3">

    <style>
        #phpinfo {padding: 1px 5px}
        #phpinfo pre {margin: 0; font-family: monospace;}
        #phpinfo a:link {color: #009; text-decoration: none; background-color: #fff;}
        #phpinfo a:hover {text-decoration: underline;}
        #phpinfo table {border-collapse: collapse; border: 0; width: 100%;}
        #phpinfo table:first-child {display: none}
        #phpinfo .center {text-align: center;}
        #phpinfo .center table {margin: 1em auto; text-align: left;}
        #phpinfo .center th {text-align: center !important;}
        #phpinfo td, th {border: 1px solid #666; font-size: 75%; vertical-align: baseline; padding: 4px 5px;}
        #phpinfo h1 {font-size: 150%;}
        #phpinfo h2 {font-size: 125%;}
        #phpinfo .p {text-align: left;}
        #phpinfo .e {background-color: #ccf; width: 300px; font-weight: bold;}
        #phpinfo .h {background-color: #99c; font-weight: bold;}
        #phpinfo .v {background-color: #ddd; max-width: 300px; overflow-x: auto; word-wrap: break-word;}
        #phpinfo .v i {color: #999;}
        #phpinfo img {float: right; border: 0;}
        #phpinfo hr {width: 100%; background-color: #ccc; border: 0; height: 1px;}
    </style>

    <div id="phpinfo">
        {!! $phpinfo !!}
    </div>
</div>
