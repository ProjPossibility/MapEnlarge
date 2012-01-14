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

#include <jni.h>
#include "string.h"
#include "net_bitquill_ocr_image_GrayImage.h"

static void throwException (JNIEnv *env, const char* ex, const char* msg)
{
    if (jclass cls = env->FindClass(ex)) {
        env->ThrowNew(cls, msg);
        env->DeleteLocalRef(cls);
    }
}

inline static int linearIndex (int width, int i, int j) {
    return i*width + j;
}

template <class T>
inline static T getPixel (const T data[], int width, int i, int j)
{
    return data[linearIndex(width, i, j)];
}

template <class T>
inline static void setPixel (T data[], int width, int i, int j, T value)
{
    data[linearIndex(width, i, j)] = value;
}

template <class T>
inline static T min (T a, T b)
{
    return (a < b) ? a : b;
}

template <class T>
inline static T max (T a, T b)
{
    return (a > b) ? a : b;
}

static void rowSumIncDec (int* sum,
        int rowInc, int rowDec,
        const unsigned char* data,
        int width, int height, int radius)
{
    int deltaInc = 0, deltaDec = 0;

    // Handle first element
    for (int sj = 0;  sj <= radius;  sj++) {
        if (rowInc < height) {
            deltaInc += getPixel(data, width, rowInc, sj);
        }
        if (rowDec >= 0) {
            deltaDec += getPixel(data, width, rowDec, sj);
        }
    }
    sum[0] += deltaInc - deltaDec;

    // Incrementally deal with remaining elements
    // Broken into three for loops to avoid if statements inside loop
    for (int j = 1;  j < radius + 1;  j++) {
        // Left edge
        if (rowInc < height) {
            deltaInc += getPixel(data, width, rowInc, j+radius);
        }
        if (rowDec >= 0) {
            deltaDec += getPixel(data, width, rowDec, j+radius);
        }
        sum[j] += deltaInc - deltaDec;
    }
    for (int j = radius + 1;  j < width - radius;  j++) {
        // Internal
        if (rowInc < height) {
            deltaInc -= getPixel(data, width, rowInc, j-radius-1);
            deltaInc += getPixel(data, width, rowInc, j+radius);
        }
        if (rowDec >= 0) {
            deltaDec -= getPixel(data, width, rowDec, j-radius-1);
            deltaDec += getPixel(data, width, rowDec, j+radius);
        }
        sum[j] += deltaInc - deltaDec;
    }
    for (int j = width - radius;  j < width;  j++) {
        // Right edge
        if (rowInc < height) {
            deltaInc -= getPixel(data, width, rowInc, j-radius-1);
        }
        if (rowDec >= 0) {
            deltaDec -= getPixel(data, width, rowDec, j-radius-1);
        }
        sum[j] += deltaInc - deltaDec;
    }
}

static void avgRow (const int* sum, int row,
        unsigned char* data, int width, int height, int radius)
{
    // Compute clipped height
    int h = min(height-1, row+radius) - max(0, row-radius) + 1;
    for (int j = 0;  j < width;  j++) {
        // Compute clipped width
        int w = min(width-1, j+radius) - max(0, j-radius) + 1;
        setPixel(data, width, row, j, (unsigned char)(sum[j]/(w*h)));
    }
}

void Java_net_bitquill_ocr_image_GrayImage_nativeMeanFilter
  (JNIEnv *env, jclass cls, jbyteArray jin, jbyteArray jout,
          jint width, jint height, jint radius)
{
    // Check parameters
    if (width < 0 || height < 0) {
        throwException(env, "java/lang/IllegalArgumentException", "Width and height must be non-negative");
        return;
    }
    if (2*radius + 1 > width || 2*radius + 1 > height) {
        throwException(env, "java/lang/IllegalArgumentException", "Radius is too large");
        return;
    }
    if (env->GetArrayLength(jin) < width*height) {
        throwException(env, "java/lang/IllegalArgumentException", "Input array too short");
        return;
    }
    if (env->GetArrayLength(jout) < width*height) {
        throwException(env, "java/lang/IllegalArgumentException", "Output array too short");
        return;
    }
    // Allocate temporary buffer
    int* sum = new int[width];
    if (sum == 0) {
        throwException(env, "java/lang/OutOfMemoryError", "Failed to allocate sums buffer");
        return;
    }
    memset(sum, 0, width * sizeof(int));  // XXX is this the C++ way?

    unsigned char *in = (unsigned char *) env->GetByteArrayElements(jin, 0);
    unsigned char *out = (unsigned char *) env->GetByteArrayElements(jout, 0);

    for (int si = 0;  si <= radius;  si++) {
        rowSumIncDec(sum, si, -1, in, width, height, radius);
    }
    avgRow(sum, 0, out, width, height, radius);

    for (int i = 1;  i < height; i++) {
        rowSumIncDec(sum, i+radius, i-radius-1, in, width, height, radius);
        avgRow(sum, i, out, width, height, radius);
    }

    delete sum;
    env->ReleaseByteArrayElements(jin, (jbyte *)in, 0);
    env->ReleaseByteArrayElements(jout, (jbyte *)out, 0);
}

static bool validateReduceParameters
  (JNIEnv *env, jbyteArray jin, jint imgWidth, jint imgHeight,
          jint left, jint top, jint width, jint height)
{
    if (imgWidth < 0 || imgHeight < 0) {
        throwException(env, "java/lang/IllegalArgumentException", "Image width and height must be non-negative");
        return false;
    }
    if (env->GetArrayLength(jin) < imgWidth * imgHeight) {
        throwException(env, "java/lang/IllegalArgumentException", "Input array too short");
        return false;
    }
    if (width < 0 || height < 0) {
        throwException(env, "java/lang/IllegalArgumentException", "ROI width and height must be non-negative");
        return false;
    }
    if (left < 0 || left + width > imgWidth || top < 0 || top + height > imgHeight) {
        throwException(env, "java/lang/IllegalArgumentException", "ROI exceeds image boundaries");
        return false;
    }
    return true;
}

template <class Reducer, class Accumulator>
static inline void submatrixReduce
  (unsigned char *in, int imgWidth, int imgHeight,
          int left, int top, int width, int height,
          Reducer reducer, Accumulator& acc)
{
    // Parameters are assumed to be validated by validateReduceParameters
    for (int i = top;  i < top + height;  i++) {
        for (int j = left;  j < left + width;  j++) {
            unsigned char val = getPixel(in, imgWidth, i, j);
            reducer(acc, val);
        }
    }
}

static struct MinReducer {
    inline void operator () (unsigned char &mn, unsigned char val) {
        mn = min(mn, val);
    }
} minReducer;

jint Java_net_bitquill_ocr_image_GrayImage_nativeMin
  (JNIEnv *env, jclass cls, jbyteArray jin, jint imgWidth, jint imgHeight,
          int left, int top, int width, int height)
{
    if (!validateReduceParameters(env, jin, imgWidth, imgHeight, left, top, width, height)) {
        return -1;
    }

    unsigned char *in = (unsigned char *) env->GetByteArrayElements(jin, 0);
    unsigned char mn = 255;
    submatrixReduce(in, imgWidth, imgHeight, left, top, width, height, minReducer, mn);
    env->ReleaseByteArrayElements(jin, (jbyte *)in, 0);

    return mn;
}

static struct MaxReducer {
    inline void operator () (unsigned char &mx, unsigned char val) {
        mx = max(mx, val);
    }
} maxReducer;

jint JNICALL Java_net_bitquill_ocr_image_GrayImage_nativeMax
  (JNIEnv *env, jclass cls, jbyteArray jin, jint imgWidth, jint imgHeight,
          int left, int top, int width, int height)
{
    if (!validateReduceParameters(env, jin, imgWidth, imgHeight, left, top, width, height)) {
        return -1;
    }

    unsigned char *in = (unsigned char *) env->GetByteArrayElements(jin, 0);
    unsigned char mx = 0;
    submatrixReduce(in, imgWidth, imgHeight, left, top, width, height, maxReducer, mx);
    env->ReleaseByteArrayElements(jin, (jbyte *)in, 0);

    return mx;
}

static struct SumReducer {
    inline void operator () (int &sum, unsigned char val) {
        sum += val;
    }
} sumReducer;

jfloat JNICALL Java_net_bitquill_ocr_image_GrayImage_nativeMean
  (JNIEnv *env, jclass cls, jbyteArray jin, jint imgWidth, jint imgHeight,
          int left, int top, int width, int height) {

    if (!validateReduceParameters(env, jin, imgWidth, imgHeight, left, top, width, height)) {
        return -1;
    }

    unsigned char *in = (unsigned char *) env->GetByteArrayElements(jin, 0);
    int sum = 0;
    submatrixReduce(in, imgWidth, imgHeight, left, top, width, height, sumReducer, sum);
    env->ReleaseByteArrayElements(jin, (jbyte *)in, 0);

    return (jfloat)sum / (width * height);
}

struct SSQ {
    int sum;
    long long sumSquares;
    SSQ () : sum(0), sumSquares(0) { }
};

static struct SSQReducer {
    inline void operator () (SSQ &ssq, unsigned char val) {
        ssq.sum += val;
        ssq.sumSquares += val*val;
    }
} ssqReducer;

jfloat JNICALL Java_net_bitquill_ocr_image_GrayImage_nativeVariance
  (JNIEnv *env, jclass cls, jbyteArray jin, jint imgWidth, jint imgHeight,
          jint left, jint top, jint width, jint height)
{

    if (!validateReduceParameters(env, jin, imgWidth, imgHeight, left, top, width, height)) {
        return -1;
    }

    unsigned char *in = (unsigned char *) env->GetByteArrayElements(jin, 0);
    SSQ ssq;
    submatrixReduce(in, imgWidth, imgHeight, left, top, width, height, ssqReducer, ssq);
    env->ReleaseByteArrayElements(jin, (jbyte *)in, 0);

    jfloat mean = (jfloat)ssq.sum / (width * height);
    return (jfloat)ssq.sumSquares / (width * height) - mean*mean;
}

static struct HistogramReducer {
    inline void operator () (jint *hist, unsigned char val) {
        ++hist[val];
    }
} histogramReducer;

void Java_net_bitquill_ocr_image_GrayImage_nativeHistogram
  (JNIEnv *env, jclass cls, jbyteArray jin, jint imgWidth, jint imgHeight,
          jintArray jout,
          jint left, jint top, jint width, jint height)
{
    if (!validateReduceParameters(env, jin, imgWidth, imgHeight, left, top, width, height)) {
        return;
    }

    unsigned char *in = (unsigned char *) env->GetByteArrayElements(jin, 0);
    jint *out = env->GetIntArrayElements(jout, 0);
    memset(out, 0, sizeof(jint)); // XXX C++ way?
    submatrixReduce(in, imgWidth, imgHeight, left, top, width, height, histogramReducer, out);
    env->ReleaseByteArrayElements(jin, (jbyte *)in, 0);
    env->ReleaseIntArrayElements(jout, (jint *)out, 0);
}

void Java_net_bitquill_ocr_image_GrayImage_nativeGrayToARGB
  (JNIEnv *env, jclass cls,
          jbyteArray jin, jint imgWidth, jint imgHeight,
          jintArray jout, jint left, jint top, jint width, jint height)
{
    // Check parameters
    if (width < 0 || height < 0 || imgWidth < 0 || imgHeight < 0) {
        throwException(env, "java/lang/IllegalArgumentException", "Width and height must be non-negative");
        return;
    }
    if (env->GetArrayLength(jout) < width * height) {
        throwException(env, "java/lang/IllegalArgumentException", "Output array too short");
        return;
    }
    if (env->GetArrayLength(jin) < imgWidth * imgHeight) {
        throwException(env, "java/lang/IllegalArgumentException", "Input array too short");
        return;
    }
    if (left < 0 || left + width >= imgWidth || top < 0 || top + height >= imgHeight) {
        throwException(env, "java/lang/IllegalArgumentException", "ROI exceeds image");
        return;
    }

    unsigned char *in = (unsigned char *) env->GetByteArrayElements(jin, 0);
    unsigned int *out = (unsigned int *) env->GetIntArrayElements(jout, 0);

    for (int i = 0;  i < height;  i++) {
        for (int j = 0;  j < width;  j++) {
            unsigned char gray = getPixel(in, imgWidth, top + i, left + j);
            unsigned int argb = (0xFF << 24) | (gray << 16) | (gray << 8) | gray;
            setPixel(out, width, i, j, argb);
        }
    }

    env->ReleaseByteArrayElements(jin, (jbyte *)in, 0);
    env->ReleaseIntArrayElements(jout, (jint *)out, 0);
}

static bool validateMapParameters
  (JNIEnv *env, jbyteArray jin, jbyteArray jout,
          jint imgWidth, jint imgHeight)
{
    if (imgWidth < 0 || imgHeight < 0) {
        throwException(env, "java/lang/IllegalArgumentException", "Image width and height must be non-negative");
        return false;
    }
    if (env->GetArrayLength(jin) < imgWidth * imgHeight) {
        throwException(env, "java/lang/IllegalArgumentException", "Input array too short");
        return false;
    }
    if (env->GetArrayLength(jout) < imgWidth * imgHeight) {
        throwException(env, "java/lang/IllegalArgumentException", "Output array too short");
        return false;
    }
    return true;
}

template <class Mapper>
static inline void matrixMap
  (unsigned char *in, unsigned char *out,
          int imgWidth, int imgHeight,
          Mapper mapper)
{
    // Parameters are assumed to be validated by validateMapParameters
    for (int i = 0;  i < imgHeight;  i++) {
        for (int j = 0;  j < imgWidth;  j++) {
            unsigned char val = getPixel(in, imgWidth, i, j);
            setPixel(out, imgWidth, i, j, mapper(i, j, val));
        }
    }
}

struct ThresholdMapper {
    unsigned char hi, lo;
    int offset;
    int imgWidth;
    unsigned char *thresh;
    ThresholdMapper (unsigned char hi, unsigned char lo, int offset,
            int imgWidth, unsigned char *thresh)
        : hi(hi), lo(lo), offset(offset), imgWidth(imgWidth), thresh(thresh) { }
    inline unsigned char operator () (int i, int j, unsigned char val) {
        int thr = getPixel(thresh, imgWidth, i, j);
        return (thr - ((int)val) < offset) ? hi : lo;
    }
};

void Java_net_bitquill_ocr_image_GrayImage_nativeAdaptiveThreshold
  (JNIEnv *env, jclass cls,
    jbyteArray jin, jbyteArray jthresh, jbyteArray jout,
    jint width, jint height, jbyte hi, jbyte lo, jint offset)
{
    // Check general parameters
    if (!validateMapParameters(env, jin, jout, width, height)) {
        return;
    }
    // Check thresholding-specific parameters
    if (env->GetArrayLength(jthresh) < width * height) {
        throwException(env, "java/lang/IllegalArgumentException", "Threshold array too short");
        return;
    }

    unsigned char *in = (unsigned char *) env->GetByteArrayElements(jin, 0);
    unsigned char *thresh = (unsigned char *) env->GetByteArrayElements(jthresh, 0);
    unsigned char *out = (unsigned char *) env->GetByteArrayElements(jout, 0);

    ThresholdMapper mapper(hi, lo, offset, width, thresh);
    matrixMap(in, out, width, height, mapper);

//    for (int i = 0;  i < height;  i++) {
//        for (int j = 0;  j < width;  j++) {
//            // Use signed ints; chars and/or unsigned may overflow
//            int val = getPixel(in, width, i, j);
//            int thr = getPixel(thresh, width, i, j);
//            setPixel(out, width, i, j, (thr - val < offset) ? (unsigned char)hi : (unsigned char)lo);
//        }
//    }

    env->ReleaseByteArrayElements(jin, (jbyte *)in, 0);
    env->ReleaseByteArrayElements(jthresh, (jbyte *)thresh, 0);
    env->ReleaseByteArrayElements(jout, (jbyte *)out, 0);
}

struct ContrastMapper {
    unsigned char mn, mx;
    unsigned int range;
    ContrastMapper (unsigned char mn, unsigned char mx)
        : mn(mn), mx(mx), range(mx - mn) { }
    inline unsigned char operator () (int i, int j, unsigned char val) {
        if (val <= mn) {
            return 0;
        } else if (val >= mx) {
            return 255;
        } else {
            return (unsigned char)(((int)(val - mn)) * 255 / range); // XXX paren galore!
        }
    }
};

void Java_net_bitquill_ocr_image_GrayImage_nativeContrastStretch
  (JNIEnv *env, jclass cls,
    jbyteArray jin, jbyteArray jout,
    jint width, jint height, jbyte mn, jbyte mx)
{
    // Check general parameters
    if (!validateMapParameters(env, jin, jout, width, height)) {
        return;
    }

    unsigned char *in = (unsigned char *) env->GetByteArrayElements(jin, 0);
    unsigned char *out = (unsigned char *) env->GetByteArrayElements(jout, 0);

    ContrastMapper mapper((unsigned char)mn, (unsigned char)mx);
    matrixMap(in, out, width, height, mapper);

    env->ReleaseByteArrayElements(jin, (jbyte *)in, 0);
    env->ReleaseByteArrayElements(jout, (jbyte *)out, 0);
}

template<class Op>
static inline unsigned char borderStructuralTransform
    (const unsigned char* in, int width, int height,
            int i0, int j0,
            int numNeighbors,
            const int *hOffsets, const int *vOffsets,
            Op op, unsigned char val0)
{
    unsigned char val = val0;
    for (int n = 0;  n < numNeighbors;  n++) {
        int i = i0 + vOffsets[n];
        int j = j0 + hOffsets[n];
        if (i >= 0 && i < height && j >= 0 && j < width) {
            val = op(val, getPixel(in, width, i, j));
        }
    }
    return val;
}

template<class Op>
static void structuralTransform
  (JNIEnv *env, jclass cls,
          jbyteArray jin, jbyteArray jout, jint width, jint height,
          jint numNeighbors,
          jintArray jhOffsets, jintArray jvOffsets,
          jintArray jlinearOffsets,
          jint minX, jint maxX, jint minY, jint maxY,
          Op op, unsigned char val0)
{
    // Check parameters
    if (env->GetArrayLength(jin) < width * height) {
        throwException(env, "java/lang/IllegalArgumentException", "Input array too short");
        return;
    }
    if (env->GetArrayLength(jout) < width * height) {
        throwException(env, "java/lang/IllegalArgumentException", "Output array too short");
        return;
    }
    if (width < 0 || height < 0) {
         throwException(env, "java/lang/IllegalArgumentException", "Width and height must be non-negative");
         return;
    }
    // TODO - more:
    // minX,minY <= 0  maxX, maxY >= 0
    // numNeighbors >= 1
    // offset array lengths == numNeighbors

    unsigned char *in = (unsigned char *) env->GetByteArrayElements(jin, 0);
    unsigned char *out = (unsigned char *) env->GetByteArrayElements(jout, 0);
    int *hOffsets = (int *) env->GetIntArrayElements(jhOffsets, 0);
    int *vOffsets = (int *) env->GetIntArrayElements(jvOffsets, 0);
    int *linearOffsets = (int *) env->GetIntArrayElements(jlinearOffsets, 0);

    // Top edge
    for (int i0 = 0;  i0 < -minY;  i0++) {
        for (int j0 = 0;  j0 < width;  j0++) {
            setPixel(out, width, i0, j0,
                    borderStructuralTransform(in, width, height,
                            i0, j0, numNeighbors, hOffsets, vOffsets, op, val0));
        }
    }
    // Bottom edge
    for (int i0 = height - maxY;  i0 < height;  i0++) {
        for (int j0 = 0;  j0 < width;  j0++) {
            setPixel(out, width, i0, j0,
                    borderStructuralTransform(in, width, height,
                            i0, j0, numNeighbors, hOffsets, vOffsets, op, val0));
        }
    }
    // Left edge
    for (int i0 = -minY;  i0 < height - maxY;  i0++) {
        for (int j0 = 0;  j0 < -minX;  j0++) {
            setPixel(out, width, i0, j0,
                    borderStructuralTransform(in, width, height,
                            i0, j0, numNeighbors, hOffsets, vOffsets, op, val0));
        }
    }
    // Right edge
    for (int i0 = -minY;  i0 < height - maxY;  i0++) {
        for (int j0 = width - maxX;  j0 < width;  j0++) {
            setPixel(out, width, i0, j0,
                    borderStructuralTransform(in, width, height,
                            i0, j0, numNeighbors, hOffsets, vOffsets, op, val0));
        }
    }

    // Interior pixels
    for (int i0 = -minY;  i0 < height - maxY;  i0++) {
        for (int j0 = -minX;  j0 < width - maxX;  j0++) {
            int lin0 = linearIndex(width, i0, j0);
            unsigned char val = in[lin0 + linearOffsets[0]];
            for (int n = 1;  n < numNeighbors;  n++) {
                val = op(val, in[lin0 + linearOffsets[n]]);
            }
            setPixel(out, width, i0, j0, val);
        }
    }

    env->ReleaseByteArrayElements(jin, (jbyte *)in, 0);
    env->ReleaseByteArrayElements(jout, (jbyte *)out, 0);
    env->ReleaseIntArrayElements (jhOffsets, (jint *)hOffsets, 0);
    env->ReleaseIntArrayElements (jvOffsets, (jint *)vOffsets, 0);
    env->ReleaseIntArrayElements (jlinearOffsets, (jint *)linearOffsets, 0);
}

static struct MinOp
{
    inline unsigned char operator () (unsigned char a, unsigned char b) {
        return (a < b) ? a : b;
    }
} minOp;

void Java_net_bitquill_ocr_image_GrayImage_nativeErode
  (JNIEnv *env, jclass cls,
          jbyteArray jin, jbyteArray jout, jint width, jint height,
          jint numNeighbors,
          jintArray jhOffsets, jintArray jvOffsets,
          jintArray jlinearOffsets,
          jint minX, jint maxX, jint minY, jint maxY)
{
    structuralTransform(env, cls,
            jin, jout, width, height,
            numNeighbors, jhOffsets, jvOffsets, jlinearOffsets,
            minX, maxX, minY, maxY,
            minOp, 255);
}

static struct maxOp
{
    inline unsigned char operator () (unsigned char a, unsigned char b) {
        return (a > b) ? a : b;
    }
} maxOp;

void Java_net_bitquill_ocr_image_GrayImage_nativeDilate
  (JNIEnv *env, jclass cls,
          jbyteArray jin, jbyteArray jout, jint width, jint height,
          jint numNeighbors,
          jintArray jhOffsets, jintArray jvOffsets,
          jintArray jlinearOffsets,
          jint minX, jint maxX, jint minY, jint maxY)
{
    structuralTransform(env, cls,
            jin, jout, width, height,
            numNeighbors, jhOffsets, jvOffsets, jlinearOffsets,
            minX, maxX, minY, maxY,
            maxOp, 0);
}
