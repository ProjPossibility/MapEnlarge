package net.bitquill.ocr;

import java.io.IOException;

import net.bitquill.ocr.image.GrayImage;
import net.bitquill.ocr.image.SimpleStructuringElement;
import net.bitquill.ocr.weocr.WeOCRClient;
import android.graphics.Bitmap;
import android.graphics.Rect;
import android.os.Bundle;
import android.os.Handler;
import android.os.HandlerThread;
import android.os.Message;
import android.util.Log;

public class OCRThread extends HandlerThread {
    private static final String TAG = OCRThread.class.getSimpleName();
    
    public static final String WORD_RECT = "word_rect";

    private static final float EXTENT_WARNING_WIDTH_FRACTION = 0.5f;
    private static final float EXTENT_WARNING_HEIGHT_FRACTION = 0.1875f;
    private static final int CONTRAST_WARNING_RANGE = 90; // XXX check value

    private boolean mEnableDump = false;
    private int mDilateRadius = OCRPreferences.PREF_DILATE_RADIUS_MEDIUM;
    
    // Image buffers used during word detection; allocated only once
    private GrayImage mBinImg = null;
    private GrayImage mResultImg = null;
    private GrayImage mTmpImg = null;

    private Handler mUIHandler;

    private Handler mHandler;
    
    @Override
    protected void onLooperPrepared () {
        mHandler = new Handler(getLooper()) {
            @Override
            public void handleMessage(Message msg) {
                switch (msg.what) {
                case R.id.msg_ocr_detect_word:
                    detectWord((byte[])msg.obj, msg.arg1, msg.arg2);
                    break;
                case R.id.msg_ocr_recognize:
                    sendOCRRequest((Bitmap)msg.obj);
                    break;
                case R.id.msg_ocr_quit:
                    getLooper().quit();
                    break;
                default:
                    super.handleMessage(msg);
                }
            }
        };
    }
    
    public OCRThread (Handler uiHandler) {
        super(TAG);
        mUIHandler = uiHandler;
    }
    
    public final Handler getHandler () {
        return mHandler;
    }
    
    public final void setPreferences (boolean enableDump, int dilateRadius) {
        mEnableDump = enableDump;
        mDilateRadius = dilateRadius;
    }
    
    private void sendOCRRequest (Bitmap textBitmap) {
        WeOCRClient weOCRClient = OCRApplication.getOCRClient();
        try {
            String ocrText = weOCRClient.doOCR(textBitmap);
            Message msg = mUIHandler.obtainMessage(R.id.msg_ui_ocr_success, ocrText);
            mUIHandler.sendMessage(msg);
        } catch (IOException ioe) {
            // TODO
            Log.e(TAG, "WeOCR failed", ioe);
            mUIHandler.sendEmptyMessage(R.id.msg_ui_ocr_fail);
        }
    }
    
    private void detectWord (byte[] yuv, int imageWidth, int imageHeight) {
        initImageBuffers(imageWidth, imageHeight);

        GrayImage img = new GrayImage(yuv, imageWidth, imageHeight);
        //long startTime = System.currentTimeMillis();
        Rect ext = makeTargetRect(imageWidth, imageHeight);
        findWordExtent(img, ext);
        //Log.d(TAG, "Find word extent in " + (System.currentTimeMillis() - startTime) + " msec");
        Log.d(TAG, "Extent is " + ext.top + "," + ext.left + "," + ext.bottom + "," + ext.right);

        boolean extentWarningActive = 
            ext.width() >= imageWidth * EXTENT_WARNING_WIDTH_FRACTION || ext.height() >= imageHeight * EXTENT_WARNING_HEIGHT_FRACTION;
        Message warningMsg = mUIHandler.obtainMessage(R.id.msg_ui_extent_warning,
                extentWarningActive ? 1 : 0, -1);
        mUIHandler.sendMessage(warningMsg);
        
        if (mEnableDump) {
            FileDumpUtil.dump("camera", img);
            FileDumpUtil.dump("bin", mResultImg);
        }

        //startTime = System.currentTimeMillis();
        Bitmap textBitmap = mResultImg.asBitmap(ext);
        //Log.d(TAG, "Converted to Bitmap in " + (System.currentTimeMillis() - startTime) + " msec");
        
        if (mEnableDump) {
            FileDumpUtil.dump("word", textBitmap);
        }
        
        Message bitmapMsg = mUIHandler.obtainMessage(R.id.msg_ui_word_bitmap, textBitmap);
        Bundle bundle = new Bundle();
        bundle.putParcelable(WORD_RECT, ext);
        bitmapMsg.setData(bundle);
        mUIHandler.sendMessage(bitmapMsg);
    }

    private static final float TARGET_HEIGHT_FRACTION = 0.033f;
    private static final float TARGET_WIDTH_FRACTION = 0.021f;
        
    private Rect makeTargetRect (int imageWidth, int imageHeight) {
        int halfWidth = (int)(TARGET_WIDTH_FRACTION * imageWidth / 2.0f);
        int halfHeight = (int)(TARGET_HEIGHT_FRACTION * imageHeight / 2.0f);
        int centerX = imageHeight / 2;
        int centerY = imageWidth / 2;
        return new Rect(centerY - halfWidth, centerX - halfHeight, 
                    centerY + halfWidth, centerX + halfHeight);
    }
    
    private void initImageBuffers (int width, int height) {
        if (mResultImg == null) {
            mBinImg = new GrayImage(width, height);
            mTmpImg = new GrayImage(width, height);
            mResultImg = new GrayImage(width, height);
        }
    }

    // Values should correspond to OCRPreferences.PREF_DILATE_RADIUS_* indices
    private static final SimpleStructuringElement[] sHStrel = {
        SimpleStructuringElement.makeHorizontal(1), 
        SimpleStructuringElement.makeHorizontal(2),
        SimpleStructuringElement.makeHorizontal(3) };
    private static final SimpleStructuringElement[] sVStrel = {
        SimpleStructuringElement.makeVertical(1),
        SimpleStructuringElement.makeVertical(2),
        SimpleStructuringElement.makeVertical(3) };

    private final void findWordExtent (GrayImage img, Rect ext) {
        GrayImage resultImg = mResultImg;
        GrayImage tmpImg = mTmpImg;
        GrayImage binImg = mBinImg;
        
        // Contrast stretch
        int imgMin = img.min(), imgMax = img.max();
        Log.d(TAG, "Image min = " + imgMin + ", max = " + imgMax);
        img.contrastStretch((byte)imgMin, (byte)imgMax, resultImg); // Temporarily store stretched image here

        // XXX - Refactor code, this shouldn't be here?
        boolean contrastWarningActive = 
            (imgMax - imgMin) <= CONTRAST_WARNING_RANGE;
        Log.d(TAG, "Contrast range = " + (imgMax - imgMin));
        Message warningMsg = mUIHandler.obtainMessage(R.id.msg_ui_contrast_warning, 
                contrastWarningActive ? 1 : 0, -1);
        mUIHandler.sendMessage(warningMsg);
        
        // Adaptive threshold
        float imgMean = resultImg.mean();
        Log.d(TAG, "Stretched image mean = " + imgMean);
        byte hi, lo;
        if (imgMean > 127) { // XXX Arbitrary threshold
            // Most likely dark text on light background
            hi = (byte)255; 
            lo = (byte)0;
        } else {
            // Most likely light text on dark background
            hi = (byte)0;
            lo = (byte)255;
        }
        resultImg.meanFilter(10, tmpImg);  // Temporarily store local means here
        int threshOffset = (int)(0.33 * Math.sqrt(resultImg.variance()));  // 0.33 pulled out of my butt
        resultImg.adaptiveThreshold(hi, lo, threshOffset, tmpImg, resultImg);

        // Dilate; it's grayscale, so we should use erosion instead
        resultImg.erode(sHStrel[mDilateRadius], tmpImg);
        tmpImg.erode(sVStrel[mDilateRadius], binImg);

        // Find word extents
        int left = ext.left, right = ext.right, top = ext.top, bottom = ext.bottom;
        int imgWidth = img.getWidth(), imgHeight = img.getHeight();
        boolean extended;
        do {
            extended = false;
            
            if ((top - 1 >= 0) && binImg.min(left, top - 1, right, top) == 0) {
                --top;
                extended = true;
            }
            if ((bottom + 1 < imgHeight) && binImg.min(left, bottom, right, bottom + 1) == 0) {
                ++bottom;
                extended = true;
            }
            if ((left - 1 >= 0) && binImg.min(left - 1, top, left, bottom) == 0) {
                --left;
                extended = true;
            }
            if ((right + 1 < imgWidth) && binImg.min(right, top, right + 1, bottom) == 0) {
                ++right;
                extended = true;
            }
        } while (extended);
        ext.set(Math.max(0, left - 2), Math.max(0, top - 2), 
                Math.min(imgWidth - 1, right + 2), Math.min(imgHeight - 1, bottom + 2));
    }

}
