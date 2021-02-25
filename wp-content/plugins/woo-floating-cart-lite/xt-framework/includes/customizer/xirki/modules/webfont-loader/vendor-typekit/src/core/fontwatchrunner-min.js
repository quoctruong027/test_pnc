goog.provide("webfont.FontWatchRunner");goog.require("webfont.Font");goog.require("webfont.FontRuler");webfont.FontWatchRunner=function(activeCallback,inactiveCallback,domHelper,font,opt_timeout,opt_metricCompatibleFonts,opt_fontTestString){this.activeCallback_=activeCallback;this.inactiveCallback_=inactiveCallback;this.domHelper_=domHelper;this.font_=font;this.fontTestString_=opt_fontTestString||webfont.FontWatchRunner.DEFAULT_TEST_STRING;this.lastResortWidths_={};this.timeout_=opt_timeout||3e3;this.metricCompatibleFonts_=opt_metricCompatibleFonts||null;this.fontRulerA_=null;this.fontRulerB_=null;this.lastResortRulerA_=null;this.lastResortRulerB_=null;this.setupRulers_()};webfont.FontWatchRunner.LastResortFonts={SERIF:"serif",SANS_SERIF:"sans-serif"};webfont.FontWatchRunner.DEFAULT_TEST_STRING="BESbswy";goog.scope(function(){var FontWatchRunner=webfont.FontWatchRunner,Font=webfont.Font,FontRuler=webfont.FontRuler;FontWatchRunner.HAS_WEBKIT_FALLBACK_BUG=null;FontWatchRunner.getUserAgent=function(){return window.navigator.userAgent};FontWatchRunner.hasWebKitFallbackBug=function(){if(FontWatchRunner.HAS_WEBKIT_FALLBACK_BUG===null){var match=/AppleWebKit\/([0-9]+)(?:\.([0-9]+))/.exec(FontWatchRunner.getUserAgent());FontWatchRunner.HAS_WEBKIT_FALLBACK_BUG=!!match&&(parseInt(match[1],10)<536||parseInt(match[1],10)===536&&parseInt(match[2],10)<=11)}return FontWatchRunner.HAS_WEBKIT_FALLBACK_BUG};FontWatchRunner.prototype.setupRulers_=function(){this.fontRulerA_=new FontRuler(this.domHelper_,this.fontTestString_);this.fontRulerB_=new FontRuler(this.domHelper_,this.fontTestString_);this.lastResortRulerA_=new FontRuler(this.domHelper_,this.fontTestString_);this.lastResortRulerB_=new FontRuler(this.domHelper_,this.fontTestString_);this.fontRulerA_.setFont(new Font(this.font_.getName()+","+FontWatchRunner.LastResortFonts.SERIF,this.font_.getVariation()));this.fontRulerB_.setFont(new Font(this.font_.getName()+","+FontWatchRunner.LastResortFonts.SANS_SERIF,this.font_.getVariation()));this.lastResortRulerA_.setFont(new Font(FontWatchRunner.LastResortFonts.SERIF,this.font_.getVariation()));this.lastResortRulerB_.setFont(new Font(FontWatchRunner.LastResortFonts.SANS_SERIF,this.font_.getVariation()));this.fontRulerA_.insert();this.fontRulerB_.insert();this.lastResortRulerA_.insert();this.lastResortRulerB_.insert()};FontWatchRunner.prototype.start=function(){this.lastResortWidths_[FontWatchRunner.LastResortFonts.SERIF]=this.lastResortRulerA_.getWidth();this.lastResortWidths_[FontWatchRunner.LastResortFonts.SANS_SERIF]=this.lastResortRulerB_.getWidth();this.started_=goog.now();this.check_()};FontWatchRunner.prototype.widthMatches_=function(width,lastResortFont){return width===this.lastResortWidths_[lastResortFont]};FontWatchRunner.prototype.widthsMatchLastResortWidths_=function(a,b){for(var font in FontWatchRunner.LastResortFonts){if(FontWatchRunner.LastResortFonts.hasOwnProperty(font)){if(this.widthMatches_(a,FontWatchRunner.LastResortFonts[font])&&this.widthMatches_(b,FontWatchRunner.LastResortFonts[font])){return true}}}return false};FontWatchRunner.prototype.hasTimedOut_=function(){return goog.now()-this.started_>=this.timeout_};FontWatchRunner.prototype.isFallbackFont_=function(a,b){return this.widthMatches_(a,FontWatchRunner.LastResortFonts.SERIF)&&this.widthMatches_(b,FontWatchRunner.LastResortFonts.SANS_SERIF)};FontWatchRunner.prototype.isLastResortFont_=function(a,b){return FontWatchRunner.hasWebKitFallbackBug()&&this.widthsMatchLastResortWidths_(a,b)};FontWatchRunner.prototype.isMetricCompatibleFont_=function(){return this.metricCompatibleFonts_===null||this.metricCompatibleFonts_.hasOwnProperty(this.font_.getName())};FontWatchRunner.prototype.check_=function(){var widthA=this.fontRulerA_.getWidth();var widthB=this.fontRulerB_.getWidth();if(this.isFallbackFont_(widthA,widthB)||this.isLastResortFont_(widthA,widthB)){if(this.hasTimedOut_()){if(this.isLastResortFont_(widthA,widthB)&&this.isMetricCompatibleFont_()){this.finish_(this.activeCallback_)}else{this.finish_(this.inactiveCallback_)}}else{this.asyncCheck_()}}else{this.finish_(this.activeCallback_)}};FontWatchRunner.prototype.asyncCheck_=function(){setTimeout(goog.bind(function(){this.check_()},this),50)};FontWatchRunner.prototype.finish_=function(callback){setTimeout(goog.bind(function(){this.fontRulerA_.remove();this.fontRulerB_.remove();this.lastResortRulerA_.remove();this.lastResortRulerB_.remove();callback(this.font_)},this),0)}});