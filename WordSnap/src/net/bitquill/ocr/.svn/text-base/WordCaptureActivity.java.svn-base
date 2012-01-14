/**
 * Copyright 2009 Spiros Papadimitriou <spapadim@cs.cmu.edu>
 * 
 * This file is part of WordSnap OCR.
 * 
 * WordSnap is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * WordSnap is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with WordSnap.  If not, see <http://www.gnu.org/licenses/>.
 */
package net.bitquill.ocr;

import android.app.Activity;
import android.app.AlertDialog;
import android.app.SearchManager;
import android.content.BroadcastReceiver;
import android.content.Context;
import android.content.DialogInterface;
import android.content.Intent;
import android.content.IntentFilter;
import android.content.SharedPreferences;
import android.graphics.Bitmap;
import android.graphics.Rect;
import android.hardware.Camera;
import android.hardware.Camera.Size;
import android.net.ConnectivityManager;
import android.net.NetworkInfo;
import android.net.Uri;
import android.os.Bundle;
import android.os.Handler;
import android.os.Message;
import android.preference.PreferenceManager;
import android.telephony.TelephonyManager;
import android.text.ClipboardManager;
import android.util.Log;
import android.view.KeyEvent;
import android.view.LayoutInflater;
import android.view.Menu;
import android.view.MenuItem;
import android.view.MotionEvent;
import android.view.SurfaceHolder;
import android.view.SurfaceView;
import android.view.View;
import android.view.Window;
import android.view.WindowManager;
import android.widget.Button;
import android.widget.EditText;
import android.widget.LinearLayout;
import android.widget.TextView;

import java.io.IOException;

import net.bitquill.ocr.image.GrayImage;
import net.bitquill.ocr.image.SimpleStructuringElement;
import net.bitquill.ocr.weocr.WeOCRClient;

public class WordCaptureActivity extends Activity implements SurfaceHolder.Callback {
    private static final String TAG = WordCaptureActivity.class.getSimpleName();
    
    private static final int TOUCH_BORDER = 20;  // How many pixels to ignore around edges
    private static final long AUTOFOCUS_MAX_WAIT_TIME = 2000L;  // How long to wait for touch-triggered AF to succeed
    private static final long CONTINUOUS_INTERVAL_TIME = 300L;  // How long to wait between image capture requests in continuous mode
    private static final int AUTOFOCUS_COUNTDOWN_INIT = 5;  // How many times to do capture before issuing a new autofocus request
    
    private static final int MENU_SETTINGS_ID = Menu.FIRST;
    private static final int MENU_ABOUT_ID = Menu.FIRST + 1;
            
    private SurfaceView mPreview;
    private boolean mHasSurface;
    private int mPreviewWidth, mPreviewHeight;
    private Camera mCamera;
    private boolean mCameraPreviewing;

    // XXX fix misnomers
    private boolean mAutoFocusInProgress;
    private boolean mPreviewCaptureInProgress;
    private boolean mProcessingInProgress;

    private int mAutoFocusCountDown;
    private boolean mUserTriggeredOCR;

    private static final int AUTOFOCUS_UNKNOWN = 0;
    private static final int AUTOFOCUS_SUCCESS = 1;
    private static final int AUTOFOCUS_FAILURE = 2;
    private int mAutoFocusStatus;
    
    private int mNetworkAlertLevel;  // If an alert's mode is <= network alert level, that alert will be shown
    
    private TextView mStatusText;
    private TextView mResultText;
    
    private LinearLayout mButtonGroup;
    private Button mWebSearchButton;
    private Button mDictionaryButton;
    private Button mClipboardButton;
    
    private static final int ID_WARNING_EXTENT = 0;
    private static final int ID_WARNING_FOCUS = 1;
    private static final int ID_WARNING_CONTRAST = 2;
    private static final int WARNING_ID_COUNT = 3;
    
    private WordGuideView mGuideView;
    
    private TextView[] mWarningViews;
    private int[] mAlertModes;
    
    private ClipboardManager mClipboardManager;
    private ConnectivityManager mConnectivityManager;
    
    private boolean mContinuousMode;
    private boolean mEnableDump;
    
    private boolean mEditBefore;
        
    private OCRThread mOCRThread; // FIXME initialize!!!!
   
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        
        Window window = getWindow();
        window.addFlags(WindowManager.LayoutParams.FLAG_FULLSCREEN |
                WindowManager.LayoutParams.FLAG_KEEP_SCREEN_ON);
        requestWindowFeature(Window.FEATURE_NO_TITLE);      
    
        setContentView(R.layout.capture);  // XXX check - use same inflater??

        mClipboardManager = (ClipboardManager)getSystemService(Context.CLIPBOARD_SERVICE);
        mConnectivityManager = (ConnectivityManager)getSystemService(Context.CONNECTIVITY_SERVICE);
        
        mPreview = (SurfaceView)findViewById(R.id.capture_surface);
        
        mPreview.setOnTouchListener(new View.OnTouchListener() {
            @Override
            public boolean onTouch(View v, MotionEvent event) {
                float x = event.getX();
                float y = event.getY();
                //int action = event.getAction();
                
                if (event.getEdgeFlags() == 0 &&
                        x > TOUCH_BORDER && y > TOUCH_BORDER &&
                        x < mPreviewWidth - TOUCH_BORDER &&
                        y < mPreviewHeight - TOUCH_BORDER) {
                    long timeSinceDown = event.getEventTime() - event.getDownTime();
                    if (mContinuousMode) {
                        // FIXME !!!!!!!!!! this logic is a f***ing mess!!
                        if (mResultText.getVisibility() == View.VISIBLE) {
                            requestAutoFocus();
                        } else {
                            mUserTriggeredOCR = true;
                        }
                        return true;
                    } else {
                        if (mAutoFocusInProgress || mPreviewCaptureInProgress || mProcessingInProgress) {
                            return false;
                        }
                        if (mAutoFocusStatus == AUTOFOCUS_SUCCESS || timeSinceDown > AUTOFOCUS_MAX_WAIT_TIME) {
                            mButtonGroup.setVisibility(View.GONE);
                            requestPreviewFrame();
                        } else {
                            requestAutoFocus();
                        }
                        return true;
                    }
                }
                return false;
            }
        });
        
        mGuideView = (WordGuideView)findViewById(R.id.guide_view);
        
        mStatusText = (TextView)findViewById(R.id.status_text);
        mResultText = (TextView)findViewById(R.id.result_text);
        mResultText.setVisibility(View.INVISIBLE);
        
        mAlertModes = new int[WARNING_ID_COUNT];
        mWarningViews = new TextView[WARNING_ID_COUNT];
        mWarningViews[ID_WARNING_FOCUS] = (TextView)findViewById(R.id.warn_focus_text);
        mWarningViews[ID_WARNING_EXTENT] = (TextView)findViewById(R.id.warn_extent_text);
        mWarningViews[ID_WARNING_CONTRAST] = (TextView)findViewById(R.id.warn_contrast_text);
        
        clearAllWarnings();
        
        mButtonGroup = (LinearLayout)findViewById(R.id.button_group);
        mWebSearchButton = (Button)findViewById(R.id.web_search_button);
        mDictionaryButton = (Button)findViewById(R.id.dictionary_button);
        mClipboardButton = (Button)findViewById(R.id.clipboard_button);
        mButtonGroup.setVisibility(View.GONE);
        mWebSearchButton.setOnClickListener(new View.OnClickListener () {
            @Override
            public void onClick(View v) {
                handleSubmitAction(R.id.msg_ui_web_search, mResultText.getText());
            }
        });
        mDictionaryButton.setOnClickListener(new View.OnClickListener () {
            @Override
            public void onClick(View v) {
                handleSubmitAction(R.id.msg_ui_wikipedia, mResultText.getText());
            }
        });
        mClipboardButton.setOnClickListener(new View.OnClickListener () {
            @Override
            public void onClick(View v) {
                handleSubmitAction(R.id.msg_ui_clipboard, mResultText.getText());
            }
        });
    }
    
    private final void handleSubmitAction (final int msgId, final CharSequence initialText) {
        if (!mEditBefore) {
            Message msg = mHandler.obtainMessage(msgId, initialText);
            mHandler.sendMessage(msg);
        } else {
            LayoutInflater inflater = LayoutInflater.from(this);
            final EditText dialogEditView = (EditText)inflater.inflate(R.layout.edit_dialog_view, null);
            Log.d(TAG, "Inflated edit dialog");
            AlertDialog dialog = new AlertDialog.Builder(this)
                .setTitle(R.string.edit_text_dialog_title)
                .setPositiveButton(R.string.ok_button, new DialogInterface.OnClickListener() {
                    @Override
                    public void onClick(DialogInterface dialog, int which) {
                        Message msg = mHandler.obtainMessage(msgId, dialogEditView.getText().toString()); // need to call toString, Editable is reset after activity finishes
                        mHandler.sendMessage(msg);
                    }
                    
                })
                .setView(dialogEditView)
                .create();
            dialogEditView.setText(initialText);
            dialog.show();
        }
    }
        
    private static final int getStringListPreference (SharedPreferences preferences, String key, String[] values, String defaultValue) {
        return linearSearch(values, preferences.getString(key, defaultValue));
    }

    private void loadPreferences () {
        SharedPreferences preferences = 
            PreferenceManager.getDefaultSharedPreferences(this);

        boolean enableDump = preferences.getBoolean(OCRPreferences.PREF_DEBUG_DUMP, false);
        if (mEnableDump) {
            FileDumpUtil.init();
        }
        int dilateRadius = getStringListPreference(preferences,
                OCRPreferences.PREF_DILATE_RADIUS, OCRPreferences.PREF_DILATE_RADIUS_VALUES,
                getString(R.string.pref_dilate_radius_default));
        mOCRThread.setPreferences(enableDump, dilateRadius);
        
        mContinuousMode = preferences.getBoolean(OCRPreferences.PREF_CONTINUOUS_MODE, true);

        mEditBefore = preferences.getBoolean(OCRPreferences.PREF_EDIT_BEFORE, false);
        mAlertModes[ID_WARNING_FOCUS] = getStringListPreference(preferences, 
                OCRPreferences.PREF_FOCUS_ALERT, OCRPreferences.PREF_ALERT_ON_WARNING_VALUES, 
                getString(R.string.pref_focus_alert_default));
        mAlertModes[ID_WARNING_EXTENT] = getStringListPreference(preferences, 
                OCRPreferences.PREF_EXTENT_ALERT, OCRPreferences.PREF_ALERT_ON_WARNING_VALUES, 
                getString(R.string.pref_extent_alert_default));
        mAlertModes[ID_WARNING_CONTRAST] = getStringListPreference(preferences, 
                OCRPreferences.PREF_CONTRAST_ALERT, OCRPreferences.PREF_ALERT_ON_WARNING_VALUES, 
                getString(R.string.pref_contrast_alert_default));
    }
        
    private void startCamera () {
        SurfaceHolder holder = mPreview.getHolder();
        if (mHasSurface) {
            Log.d(TAG, "startCamera after pause");
            // Resumed after pause, surface already exists
            surfaceCreated(holder);
            startCameraPreview();
        } else {
            Log.d(TAG, "startCamera from scratch");
            // Install a SurfaceHolder.Callback so we get notified when the
            // underlying surface is created and destroyed.
            holder.addCallback(this);
            holder.setType(SurfaceHolder.SURFACE_TYPE_PUSH_BUFFERS);
        }
    }
    
    private void startCameraPreview () {
        if (!mCameraPreviewing) {
            mCamera.startPreview();
            mCameraPreviewing = true;
            if (mContinuousMode) {
                requestAutoFocus();  // Trigger continuous capture
            }
        }
    }
    
    private void stopCameraPreview () {
        if (mCameraPreviewing) {
            mCamera.stopPreview();
            mCameraPreviewing = false;
        }
    }
    
    private void stopCamera () {
        if (mCamera != null) {
            stopCameraPreview();
            mCamera.release();
            mCamera = null;
        }
    }
    
    private void requestAutoFocus () {
        if (mAutoFocusInProgress || mPreviewCaptureInProgress) {
            return;
        }
        mAutoFocusStatus = AUTOFOCUS_UNKNOWN;
        mAutoFocusInProgress = true;
        mCamera.autoFocus(new Camera.AutoFocusCallback() { 
            @Override
            public void onAutoFocus(boolean success, Camera camera) {
                Message msg = mHandler.obtainMessage(R.id.msg_camera_auto_focus, 
                        success ? AUTOFOCUS_SUCCESS : AUTOFOCUS_FAILURE, -1);
                mHandler.sendMessage(msg);
            }
        });
    }
    
    private void requestPreviewFrame () {
        if (mAutoFocusInProgress || mPreviewCaptureInProgress) {
            return;
        }
        mPreviewCaptureInProgress = true;
        mCamera.setOneShotPreviewCallback(new Camera.PreviewCallback() {
            @Override
            public void onPreviewFrame(byte[] data, Camera camera) {
                Message msg = mHandler.obtainMessage(R.id.msg_camera_preview_frame, data);
                mHandler.sendMessage(msg);
            }
        });
    }
    
    private void initStateVariables () {
        mAutoFocusStatus = AUTOFOCUS_UNKNOWN;
        mAutoFocusInProgress = false;
        mPreviewCaptureInProgress = false;
        mProcessingInProgress = false;
        mUserTriggeredOCR = false;
        mAutoFocusCountDown = AUTOFOCUS_COUNTDOWN_INIT;
    }
    
    private void startOCRThread () {
        assert(mOCRThread == null);
        mOCRThread = new OCRThread(mHandler);
        mOCRThread.start();
    }
    
    private void stopOCRThread () {
        if (mOCRThread != null) {
            mOCRThread.getHandler().sendEmptyMessage(R.id.msg_ocr_quit);
            try {
                mOCRThread.join();
            } catch (InterruptedException ie) { }
            mOCRThread = null;
            // Don't send any messages that will cause a NullPointerException
            mHandler.removeMessages(R.id.msg_camera_preview_frame);
        }
    }
    
    @Override
    protected void onResume() {
        Log.d(TAG, "onResume");
        startOCRThread();

        loadPreferences();
        
        updateNetworkAlertLevel();
        IntentFilter filter = new IntentFilter(ConnectivityManager.CONNECTIVITY_ACTION);
        registerReceiver(mConnectivityReceiver, filter, null, mHandler);
        
        initStateVariables();
        super.onResume();
        startCamera();
    }

    @Override
    protected void onPause() {
        Log.d(TAG, "onPause");
        super.onPause();
        unregisterReceiver(mConnectivityReceiver);
        stopCamera();
        stopOCRThread();
    }
    
    @Override
    public boolean onCreateOptionsMenu(Menu menu) {
      super.onCreateOptionsMenu(menu);
      menu.add(0, MENU_SETTINGS_ID, 0, R.string.menu_settings)
          .setIcon(android.R.drawable.ic_menu_preferences);
      menu.add(0, MENU_ABOUT_ID, 0, R.string.menu_about)
          .setIcon(android.R.drawable.ic_menu_info_details);
      return true;
    }

    @Override
    public boolean onKeyDown(int keyCode, KeyEvent event) {
        if (keyCode == KeyEvent.KEYCODE_FOCUS) {
            if (event.getRepeatCount() == 0) {
                mButtonGroup.setVisibility(View.GONE);
                mHandler.removeMessages(R.id.msg_request_delayed_capture);
                mAutoFocusCountDown = AUTOFOCUS_COUNTDOWN_INIT;
                requestAutoFocus();
            }
            return true;
        } else if (keyCode == KeyEvent.KEYCODE_CAMERA) {
            if (event.getRepeatCount() == 0) {
                if (mContinuousMode) {
                    mUserTriggeredOCR = true;
                } else {
                    requestPreviewFrame();
                }
            }
            return true;
        } else {
            return super.onKeyDown(keyCode, event);
        }
    }
    
    @Override
    public boolean onOptionsItemSelected(MenuItem item) {
        switch (item.getItemId()) {
        case MENU_SETTINGS_ID:
            startActivity(new Intent(this, OCRPreferences.class));
            return true;
        case MENU_ABOUT_ID:
            startActivity(new Intent(Intent.ACTION_VIEW, Uri.parse(getString(R.string.about_url))));
            return true;
        }
        return super.onOptionsItemSelected(item);
    }

    public void surfaceCreated(SurfaceHolder holder) {
        // The Surface has been created, acquire the camera 
        // and tell it where to draw.
        mCamera = Camera.open();
        try {
           mCamera.setPreviewDisplay(holder);
           Log.d(TAG, "surfaceCreated: setPreviewDisplay");
        } catch (IOException e) {
            Log.e(TAG, "Camera preview failed", e);
            mCamera.release();
            mCamera = null;
            // TODO add more exception handling logic here
        }
        mHasSurface = true;
    }

    public void surfaceDestroyed(SurfaceHolder holder) {
        // Surface will be destroyed when we return, so stop the preview.
        // Because the CameraDevice object is not a shared resource, it's very
        // important to release it when the activity is paused.
        stopCamera();
        mHasSurface = false;
    }

    public void surfaceChanged(SurfaceHolder holder, int format, int w, int h) {
        // Size is known: set up camera parameters for preview size
        Camera.Parameters params = mCamera.getParameters();
        params.setPreviewSize(w, h);
        mCamera.setParameters(params);
        
        // Get parameters back; actual preview size may differ
        params = mCamera.getParameters();
        Size sz = params.getPreviewSize();
        mPreviewWidth = sz.width;
        mPreviewHeight = sz.height;

        startCameraPreview();
        
        Log.d(TAG, "surfaceChanged: startPreview");
    }
    
    private void sendOCRRequest (final Bitmap textBitmap) {
        mStatusText.setText(R.string.status_processing_text);
        Handler ocrHandler = mOCRThread.getHandler();
        Message ocrMessage = ocrHandler.obtainMessage(R.id.msg_ocr_recognize, textBitmap);
        ocrHandler.sendMessage(ocrMessage);
    }
    
    private void setWarning (int warningId, boolean active) {
        mWarningViews[warningId].setVisibility(active ? View.VISIBLE : View.GONE);
    }
    
    private boolean getWarning (int warningId) {
        return mWarningViews[warningId].getVisibility() == View.VISIBLE;
    }
    
    private void clearAllWarnings () {
        for (int id = 0;  id < WARNING_ID_COUNT;  id++) {
            setWarning(id, false);
        }
    }
   
    /**
     * Try to determine whether a warning alert should be shown, depending on network connectivity.
     * If the alert mode for a particular warning is less than or equal to the network alert level,
     * then an alert dialog should be shown before submitting data to the OCR web service.
     */
    private void updateNetworkAlertLevel () {
        NetworkInfo netInfo = mConnectivityManager.getActiveNetworkInfo();
        if (netInfo == null) {
            // Meaning not to be confused: "show alert only if level is set to 'always show'"
            mNetworkAlertLevel = OCRPreferences.PREF_ALERT_ALWAYS;
        } else {
            int netType = netInfo.getType();
            int netSubtype = netInfo.getSubtype();
            switch (netType) {
            case ConnectivityManager.TYPE_WIFI:
                mNetworkAlertLevel = OCRPreferences.PREF_ALERT_ALWAYS;  // see above for meaning
                break;
            case ConnectivityManager.TYPE_MOBILE:
                if (netSubtype == TelephonyManager.NETWORK_TYPE_UMTS) {
                    mNetworkAlertLevel = OCRPreferences.PREF_ALERT_3G;
                } else {
                    mNetworkAlertLevel = OCRPreferences.PREF_ALERT_EDGE;
                }
                break;
            default:
                Log.e(TAG, "Unknown network connectivity type: " + netInfo.getTypeName());
                mNetworkAlertLevel = OCRPreferences.PREF_ALERT_ALWAYS; // arbitrary default
                break;
            }
        }
        Log.d(TAG, "Set network alert level to " + mNetworkAlertLevel);
    }
    
    private final BroadcastReceiver mConnectivityReceiver = new BroadcastReceiver () {
        @Override
        public void onReceive(Context context, Intent intent) {
            Log.d(TAG, "Connectivity action broadcast");
            updateNetworkAlertLevel();
        }
    };
    
    private final Handler mHandler = new Handler () {
        @Override
        public void handleMessage(Message msg) {
            boolean continuousMode = mContinuousMode;
            switch (msg.what) {
            case R.id.msg_camera_auto_focus:
                mAutoFocusStatus = msg.arg1;
                mAutoFocusInProgress = false;
                clearAllWarnings(); // FIXME - sort out the warning state logic!!!
                // We could do this without a separate message, but sending one anyway for consistency...
                boolean focusWarningActive = 
                    mAutoFocusStatus != AUTOFOCUS_SUCCESS;
                Message warningMsg = mHandler.obtainMessage(R.id.msg_ui_focus_warning,
                        focusWarningActive ? 1 : 0, -1);
                mHandler.sendMessage(warningMsg);
                if (continuousMode) {
                    requestPreviewFrame();
                }
                break;
            case R.id.msg_camera_preview_frame:
                mProcessingInProgress = true;
                Handler ocrHandler = mOCRThread.getHandler();
                Message preprocessMsg = ocrHandler.obtainMessage(R.id.msg_ocr_detect_word, mPreviewWidth, mPreviewHeight, msg.obj);
                ocrHandler.sendMessage(preprocessMsg);
                mButtonGroup.setVisibility(View.GONE);
                mResultText.setVisibility(View.INVISIBLE);
                //mStatusText.setText(R.string.status_preprocessing_text);
                break;
            case R.id.msg_request_delayed_capture:
                if (mOCRThread != null) {
                    requestPreviewFrame();
                }
                break;
            case R.id.msg_ui_word_bitmap:
                mPreviewCaptureInProgress = false;
                final Bitmap textBitmap = (Bitmap)msg.obj;
                
                final Bundle bundle = msg.getData();
                final Rect wordExt = bundle.getParcelable(OCRThread.WORD_RECT);
                mGuideView.setExtentRect(getWarning(ID_WARNING_EXTENT) ? null : wordExt);
                
                if (continuousMode && !mUserTriggeredOCR) {
                    if (--mAutoFocusCountDown < 0 || mAutoFocusStatus != AUTOFOCUS_SUCCESS) {
                        mAutoFocusCountDown = AUTOFOCUS_COUNTDOWN_INIT;
                        requestAutoFocus();
                    } else {
                        mHandler.sendEmptyMessageDelayed(R.id.msg_request_delayed_capture, CONTINUOUS_INTERVAL_TIME);
                    }
                    break; 
                }
                mUserTriggeredOCR = false;  // for the next time around
                
                int networkAlertLevel = mNetworkAlertLevel;
                int[] alertModes = mAlertModes;
                //Log.d(TAG, "network alert level = " + networkAlertLevel);
                String alertMessage = null;
                if (alertModes[ID_WARNING_EXTENT] >= networkAlertLevel && getWarning(ID_WARNING_EXTENT)) {
                    alertMessage = getString(R.string.extent_warning_alert_message);
                } else if (alertModes[ID_WARNING_CONTRAST] >= networkAlertLevel && getWarning(ID_WARNING_CONTRAST)) {
                    alertMessage = getString(R.string.contrast_warning_alert_message);
                } else if (alertModes[ID_WARNING_FOCUS] >= networkAlertLevel && getWarning(ID_WARNING_FOCUS)) {
                    alertMessage = getString(R.string.focus_warning_alert_message);
                }
                if (alertMessage != null) {
                    // Defer sending, only after user confirms
                    AlertDialog dialog = new AlertDialog.Builder(WordCaptureActivity.this)
                        .setTitle(R.string.warning_alert_dialog_title)
                        .setMessage(alertMessage)
                        .setPositiveButton(R.string.send_anyway_button, new DialogInterface.OnClickListener() {
                            @Override
                            public void onClick(DialogInterface dialog, int which) {
                                sendOCRRequest(textBitmap);
                            }
                        })
                        .setNegativeButton(R.string.retake_photo_button, new DialogInterface.OnClickListener() {
                            @Override
                            public void onClick (DialogInterface dialog, int which) {
                                // Reset status text
                                mStatusText.setText(R.string.status_guide_text);
                            }
                        })
                        .create();
                    dialog.show();
                } else {
                    // Send anyway
                    sendOCRRequest(textBitmap);
                }
                break;
            case R.id.msg_ui_ocr_success:
                final String ocrText = (String)msg.obj;
                Log.i(TAG, "OCR result text: " + ocrText);
                // Toast fails from this thread
                //Toast.makeText(WordCaptureActivity.this, "OCR result: " + ocrText, Toast.LENGTH_LONG)
                //     .show();
                mStatusText.setText(R.string.status_finished_text);
                mGuideView.setExtentRect(null);
                mResultText.setText(ocrText);
                mResultText.setVisibility(View.VISIBLE);
                mButtonGroup.setVisibility(View.VISIBLE);
                mProcessingInProgress = false;
                mAutoFocusStatus = AUTOFOCUS_UNKNOWN;
                mHandler.sendEmptyMessageDelayed(R.id.msg_ui_reset_status, 2000L);
                break;
            case R.id.msg_ui_ocr_fail:
                mStatusText.setText(R.string.status_processing_error_text);
                mGuideView.setExtentRect(null);
                mProcessingInProgress = false;
                mAutoFocusStatus = AUTOFOCUS_UNKNOWN;
                //mHandler.sendEmptyMessageDelayed(R.id.msg_reset_status, 5000L);
                break;
            case R.id.msg_ui_reset_status:
                //mResultText.setVisibility(View.INVISIBLE);
                mStatusText.setText(R.string.status_guide_text);
                break;
            case R.id.msg_ui_extent_warning:
                setWarning(ID_WARNING_EXTENT, msg.arg1 != 0);
                break;
            case R.id.msg_ui_contrast_warning:
                setWarning(ID_WARNING_CONTRAST, msg.arg1 != 0);
                break;
            case R.id.msg_ui_focus_warning:
                setWarning(ID_WARNING_FOCUS, msg.arg1 != 0);
                break;
            case R.id.msg_ui_web_search:
                Log.d(TAG, "msg_ui_web_search obj=" + (CharSequence)msg.obj);
                Intent webSearchIntent = new Intent(Intent.ACTION_WEB_SEARCH);
                webSearchIntent.putExtra(SearchManager.QUERY, (CharSequence)msg.obj);
                startActivity(webSearchIntent);
                finish();
                break;
            case R.id.msg_ui_wikipedia:
                Uri wikipediaUrl = Uri.parse("http://en.m.wikipedia.org/wiki?search=" + (CharSequence)msg.obj);
                Intent wikipediaIntent = new Intent(Intent.ACTION_VIEW, wikipediaUrl);
                startActivity(wikipediaIntent);
                finish();
                break;
            case R.id.msg_ui_clipboard:
                mClipboardManager.setText((CharSequence)msg.obj);
                finish();
                break;
            default:
                super.handleMessage(msg);
            }
        }
    };    

    /**
     * Utility function to do simple linear search over an array of Objects.
     * Comparison uses {@link Object#equals(Object)} method.
     * 
     * @param a    Array to search
     * @param key  Object value to look for
     * @return     Index of object, or -1 if not found
     */
    private static final int linearSearch (Object[] a, Object key) {
        for (int i = 0;  i < a.length;  i++) {
            if (a[i].equals(key)) {
                return i;
            }
        }
        return -1;
    }

}